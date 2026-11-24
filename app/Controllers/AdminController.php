<?php

namespace App\Controllers;

use App\Models\RequestModel;
use App\Models\UserModel;
use App\Models\CategoryModel;
use App\Models\ActivityLogModel;

class AdminController extends BaseController
{
    public function dashboard()
    {
        $reqModel   = new RequestModel();
        $counts     = $reqModel->getDashboardCounts();
        $chart      = $reqModel->getCategoryBreakdown();
        $logModel   = new ActivityLogModel();
        $recentLogs = $logModel->getAllLogs(10);
        $recentRequests = $reqModel->getWithDetails([], '', 5);
        $overdueRequest = db_connect()->table('requests')
            ->select('id')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->where('due_at <', date('Y-m-d H:i:s'))
            ->where('due_at IS NOT NULL', null, false)
            ->orderBy('due_at', 'ASC')
            ->get(1)->getRowArray();
        $overdueRequest = $overdueRequest
            ? $reqModel->getOneWithDetails((int)$overdueRequest['id'])
            : null;
        return view('admin/dashboard', compact('counts', 'chart', 'recentLogs', 'recentRequests', 'overdueRequest'));
    }

    public function users()
    {
        $users = db_connect()->table('users u')
            ->select('u.*, c.name as category_name')
            ->join('categories c', 'c.id = u.category_id', 'left')
            ->orderBy('u.name', 'ASC')
            ->get()->getResultArray();
        return view('admin/users', ['users' => $users]);
    }

    public function createUserForm()
    {
        return view('admin/user_form', ['user' => null, 'categories' => (new CategoryModel())->findAll()]);
    }

    public function storeUser()
    {
        $userModel = new UserModel();
        $role = (string)$this->request->getPost('role');
        $categoryId = $this->request->getPost('category_id');
        $password = (string)$this->request->getPost('password');
        if (strlen($password) < 8) {
            return redirect()->back()->withInput()->with('error', 'Passwords must be at least 8 characters long.');
        }
        if ($role === 'agent' && !(new CategoryModel())->find((int)$categoryId)) {
            return redirect()->back()->withInput()->with('error', 'Assign each agent to a valid service category.');
        }
        $data = [
            'name'        => trim((string)$this->request->getPost('name')),
            'email'       => trim((string)$this->request->getPost('email')),
            'password'    => password_hash($password, PASSWORD_DEFAULT),
            'role'        => $role,
            'category_id' => $role === 'agent' ? (int)$categoryId : null,
        ];
        if (!$userModel->insert($data)) {
            $errors = $userModel->errors();
            return redirect()->back()->withInput()->with('error', $errors ? implode(' ', $errors) : 'The user could not be created.');
        }
        return redirect()->to(site_url('admin/users'))->with('success', 'User created successfully.');
    }

    public function editUserForm(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->to(site_url('admin/users'))->with('error', 'User not found.');
        }
        return view('admin/user_form', ['user' => $user, 'categories' => (new CategoryModel())->findAll()]);
    }

    public function updateUser(int $id)
    {
        $userModel = new UserModel();
        $role = (string)$this->request->getPost('role');
        $categoryId = $this->request->getPost('category_id');
        if ($role === 'agent' && !(new CategoryModel())->find((int)$categoryId)) {
            return redirect()->back()->withInput()->with('error', 'Assign each agent to a valid service category.');
        }
        $data = [
            'name'        => trim((string)$this->request->getPost('name')),
            'email'       => trim((string)$this->request->getPost('email')),
            'role'        => $role,
            'category_id' => $role === 'agent' ? (int)$categoryId : null,
        ];
        $password = (string)$this->request->getPost('password');
        if ($password !== '') {
            if (strlen($password) < 8) {
                return redirect()->back()->withInput()->with('error', 'Passwords must be at least 8 characters long.');
            }
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        if (!$userModel->update($id, $data)) {
            $errors = $userModel->errors();
            return redirect()->back()->withInput()->with('error', $errors ? implode(' ', $errors) : 'The user could not be updated.');
        }
        return redirect()->to(site_url('admin/users'))->with('success', 'User updated successfully.');
    }

    public function deleteUser(int $id)
    {
        (new UserModel())->delete($id);
        return redirect()->to(site_url('admin/users'))->with('success', 'User deleted successfully.');
    }

    public function categories()
    {
        $catModel = new CategoryModel();
        return view('admin/categories', ['categories' => $catModel->findAll()]);
    }

    public function createCategoryForm()
    {
        return view('admin/category_form', ['category' => null]);
    }

    public function storeCategory()
    {
        $catModel = new CategoryModel();
        $data = [
            'name'      => trim((string)$this->request->getPost('name')),
            'sla_hours' => (int)$this->request->getPost('sla_hours'),
        ];
        if ($data['name'] === '' || $data['sla_hours'] < 1 || !$catModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'Enter a category name and an SLA of at least one hour.');
        }
        return redirect()->to(site_url('admin/categories'))->with('success', 'Category created successfully.');
    }

    public function editCategoryForm(int $id)
    {
        $category = (new CategoryModel())->find($id);
        if (!$category) {
            return redirect()->to(site_url('admin/categories'))->with('error', 'Category not found.');
        }
        return view('admin/category_form', ['category' => $category]);
    }

    public function updateCategory(int $id)
    {
        $categoryModel = new CategoryModel();
        $data = [
            'name'      => trim((string)$this->request->getPost('name')),
            'sla_hours' => (int)$this->request->getPost('sla_hours'),
        ];
        if ($data['name'] === '' || $data['sla_hours'] < 1 || !$categoryModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', 'Enter a category name and an SLA of at least one hour.');
        }
        return redirect()->to(site_url('admin/categories'))->with('success', 'Category updated successfully.');
    }

    public function activityLog()
    {
        $logs = (new ActivityLogModel())->getAllLogs(200);
        return view('admin/activity_log', ['logs' => $logs]);
    }
}
