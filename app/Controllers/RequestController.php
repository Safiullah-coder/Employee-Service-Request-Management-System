<?php

namespace App\Controllers;

use App\Models\RequestModel;
use App\Models\CategoryModel;
use App\Models\CommentModel;
use App\Models\ActivityLogModel;
use App\Models\UserModel;

class RequestController extends BaseController
{
    protected RequestModel $reqModel;
    protected CategoryModel $catModel;
    protected CommentModel $comModel;
    protected ActivityLogModel $logModel;

    public function __construct()
    {
        $this->reqModel = new RequestModel();
        $this->catModel = new CategoryModel();
        $this->comModel = new CommentModel();
        $this->logModel = new ActivityLogModel();
    }

    // ─── EMPLOYEE: My Requests ───────────────────────────────────────
    public function myRequests()
    {
        $userId = session()->get('user_id');
        $search = trim((string)$this->request->getGet('q'));
        $requests = $this->reqModel->getWithDetails(['r.user_id' => $userId], $search);
        return view('requests/my_requests', compact('requests', 'search'));
    }

    // ─── EMPLOYEE: Create Request ────────────────────────────────────
    public function create()
    {
        $categories = $this->catModel->findAll();
        return view('requests/create', ['categories' => $categories]);
    }

    public function store()
    {
        $userId     = session()->get('user_id');
        $categoryId = (int)$this->request->getPost('category_id');
        $priority   = (string)$this->request->getPost('priority');
        $title      = trim((string)$this->request->getPost('title'));
        $description = trim((string)$this->request->getPost('description'));

        $category = $this->catModel->find($categoryId);
        if (!$category || $title === '' || mb_strlen($title) > 200 || !in_array($priority, ['low', 'medium', 'high'], true)) {
            return redirect()->back()->withInput()->with('error', 'Please provide a valid title, category, and priority.');
        }

        $agent = $this->reqModel->getAgentForCategory($categoryId);
        if (!$agent) {
            return redirect()->back()->withInput()->with('error', 'No agent is assigned to this category yet. Please contact an administrator.');
        }

        $dueAt = RequestModel::calcDueAt((int)$category['sla_hours'], $priority);

        $data = [
            'title'       => $title,
            'description' => $description,
            'category_id' => $categoryId,
            'priority'    => $priority,
            'status'      => 'open',
            'user_id'     => $userId,
            'assigned_to' => $agent['id'],
            'due_at'      => $dueAt,
        ];

        $db = db_connect();
        $db->transBegin();
        $reqId = $this->reqModel->insert($data);
        $logged = $reqId
            && $this->logModel->logChange($reqId, $userId, 'status', '', 'open')
            && $this->logModel->logChange($reqId, $userId, 'assigned_to', '', $agent['name']);
        if (!$logged || !$db->transStatus()) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'The request could not be created. Please try again.');
        }
        $db->transCommit();

        return redirect()->to(site_url('my-requests'))->with('success', 'Your request has been submitted.');
    }

    // ─── SHARED: View Single Request ────────────────────────────────
    public function show(int $id)
    {
        $request = $this->reqModel->getOneWithDetails($id);
        if (!$request) return redirect()->back()->with('error', 'Request not found.');

        $role   = session()->get('role');
        $userId = session()->get('user_id');

        if (($role === 'employee' && (int)$request['user_id'] !== (int)$userId)
            || ($role === 'agent' && (int)$request['assigned_to'] !== (int)$userId)) {
            return redirect()->to(site_url('dashboard'))->with('error', 'You do not have permission to view this request.');
        }

        $comments  = $this->comModel->getForRequest($id);
        $logs      = $this->logModel->getForRequest($id);
        $isOverdue = RequestModel::isOverdue($request);
        $agents = $role === 'admin'
            ? (new UserModel())->where('role', 'agent')->where('category_id', $request['category_id'])->findAll()
            : [];

        return view('requests/show', compact('request', 'comments', 'logs', 'isOverdue', 'agents'));
    }

    // ─── SHARED: Add Comment ─────────────────────────────────────────
    public function addComment(int $id)
    {
        $userId  = session()->get('user_id');
        $comment = trim((string)$this->request->getPost('comment'));

        if ($comment === '' || mb_strlen($comment) > 5000) {
            return redirect()->back()->with('error', 'Comments must contain between 1 and 5,000 characters.');
        }

        $request = $this->reqModel->find($id);
        if (!$request) {
            return redirect()->back()->with('error', 'Request not found.');
        }
        $role = session()->get('role');
        if (($role === 'employee' && (int)$request['user_id'] !== (int)$userId)
            || ($role === 'agent' && (int)$request['assigned_to'] !== (int)$userId)) {
            return redirect()->to(site_url('dashboard'))->with('error', 'You do not have permission to comment on this request.');
        }

        $db = db_connect();
        $db->transBegin();
        $saved = $this->comModel->insert([
            'request_id' => $id,
            'user_id'    => $userId,
            'comment'    => $comment,
        ]) && $this->logModel->logChange($id, $userId, 'comment', '', mb_substr($comment, 0, 250));
        if (!$saved || !$db->transStatus()) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Your comment could not be saved. Please try again.');
        }
        $db->transCommit();

        return redirect()->to(site_url('requests/' . $id))->with('success', 'Your comment has been added.');
    }

    // ─── AGENT: All Requests ─────────────────────────────────────────
    public function agentRequests()
    {
        $search = trim((string)$this->request->getGet('q'));
        $requests = $this->reqModel->getWithDetails(
            ['r.assigned_to' => session()->get('user_id')],
            $search
        );
        return view('requests/agent_list', compact('requests', 'search'));
    }

    // ─── AGENT: Update Status ─────────────────────────────────────────
    public function updateStatus(int $id)
    {
        $userId    = session()->get('user_id');
        $newStatus = (string)$this->request->getPost('status');
        $request   = $this->reqModel->find($id);

        if (!$request) {
            return redirect()->back()->with('error', 'Request not found.');
        }
        if (session()->get('role') === 'agent' && (int)$request['assigned_to'] !== (int)$userId) {
            return redirect()->to(site_url('dashboard'))->with('error', 'You can only update requests assigned to you.');
        }

        if (!RequestModel::isValidTransition($request['status'], $newStatus)) {
            return redirect()->back()->with('error', 'Invalid status change. Requests must follow: Open → In Progress → Resolved → Closed.');
        }

        $db = db_connect();
        $db->transBegin();
        $saved = $this->reqModel->update($id, ['status' => $newStatus])
            && $this->logModel->logChange($id, $userId, 'status', $request['status'], $newStatus);
        if (!$saved || !$db->transStatus()) {
            $db->transRollback();
            return redirect()->back()->with('error', 'The status change could not be saved. Please try again.');
        }
        $db->transCommit();

        return redirect()->to(site_url('requests/' . $id))->with('success', 'Request status updated.');
    }

    // ─── ADMIN: All Requests ─────────────────────────────────────────
    public function adminRequests()
    {
        $where = [];
        if ($status = $this->request->getGet('status')) {
            $where['r.status'] = $status;
        }
        $search = trim((string)$this->request->getGet('q'));
        $requests = $this->reqModel->getWithDetails($where, $search);
        return view('requests/admin_list', compact('requests', 'search'));
    }

    // ─── ADMIN: Reassign Request ─────────────────────────────────────
    public function reassign(int $id)
    {
        $userId  = session()->get('user_id');
        $agentId = (int)$this->request->getPost('assigned_to');
        $request = $this->reqModel->find($id);

        if (!$request) {
            return redirect()->back()->with('error', 'Request not found.');
        }
        $agent = (new UserModel())->where('id', $agentId)
            ->where('role', 'agent')
            ->where('category_id', $request['category_id'])
            ->first();
        if (!$agent) {
            return redirect()->back()->with('error', 'Select an agent assigned to this request category.');
        }

        $previousAgent = $request['assigned_to']
            ? (new UserModel())->find($request['assigned_to'])
            : null;
        $db = db_connect();
        $db->transBegin();
        $saved = $this->reqModel->update($id, ['assigned_to' => $agentId])
            && $this->logModel->logChange($id, $userId, 'assigned_to', $previousAgent['name'] ?? '', $agent['name']);
        if (!$saved || !$db->transStatus()) {
            $db->transRollback();
            return redirect()->back()->with('error', 'The assignment could not be saved. Please try again.');
        }
        $db->transCommit();

        return redirect()->to(site_url('requests/' . $id))->with('success', 'Request assignment updated.');
    }
}
