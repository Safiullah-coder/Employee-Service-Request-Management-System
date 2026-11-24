<?php

namespace App\Models;

use CodeIgniter\Model;

class RequestModel extends Model
{
    protected $table      = 'requests';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['title', 'description', 'category_id', 'priority', 'status', 'user_id', 'assigned_to', 'due_at'];
    protected $useTimestamps = true;

    public const STATUS_FLOW = ['open', 'in_progress', 'resolved', 'closed'];

    public static function calcDueAt(int $slaHours, string $priority): string
    {
        $hours = $priority === 'high' ? $slaHours / 2 : $slaHours;
        return date('Y-m-d H:i:s', time() + (int) round($hours * 3600));
    }

    public static function isOverdue(array $request): bool
    {
        if (in_array($request['status'], ['resolved', 'closed'])) return false;
        if (empty($request['due_at'])) return false;
        return strtotime($request['due_at']) < time();
    }

    public static function isValidTransition(string $current, string $next): bool
    {
        $flow = self::STATUS_FLOW;
        $ci = array_search($current, $flow);
        $ni = array_search($next, $flow);
        return ($ci !== false && $ni !== false && $ni === $ci + 1);
    }

    public function getAgentForCategory(int $categoryId): ?array
    {
        $agent = $this->db->table('users')
            ->select('id, name')
            ->where('role', 'agent')
            ->where('category_id', $categoryId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        return $agent ?: null;
    }

    public function getWithDetails(array $where = [], string $search = '', ?int $limit = null)
    {
        $builder = $this->db->table('requests r')
            ->select('r.*, u.name as requester_name, a.name as agent_name, c.name as category_name, c.sla_hours')
            ->join('users u', 'u.id = r.user_id', 'left')
            ->join('users a', 'a.id = r.assigned_to', 'left')
            ->join('categories c', 'c.id = r.category_id', 'left');
        foreach ($where as $key => $val) {
            $builder->where($key, $val);
        }
        if ($search !== '') {
            $builder->groupStart()
                ->like('r.title', $search)
                ->orLike('r.description', $search)
                ->orLike('u.name', $search)
                ->orLike('c.name', $search)
                ->groupEnd();
        }
        $builder->orderBy('r.created_at', 'DESC');
        if ($limit !== null) {
            $builder->limit($limit);
        }
        return $builder->get()->getResultArray();
    }

    public function getOneWithDetails(int $id): ?array
    {
        $result = $this->db->table('requests r')
            ->select('r.*, u.name as requester_name, a.name as agent_name, c.name as category_name, c.sla_hours')
            ->join('users u', 'u.id = r.user_id', 'left')
            ->join('users a', 'a.id = r.assigned_to', 'left')
            ->join('categories c', 'c.id = r.category_id', 'left')
            ->where('r.id', $id)->get()->getRowArray();
        return $result ?: null;
    }

    public function getDashboardCounts(): array
    {
        $counts = [];
        foreach (self::STATUS_FLOW as $status) {
            $counts[$status] = $this->where('status', $status)->countAllResults();
        }
        $counts['overdue'] = $this->db->table('requests')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->where('due_at <', date('Y-m-d H:i:s'))
            ->where('due_at IS NOT NULL', null, false)
            ->countAllResults();
        $counts['total'] = $this->countAllResults();
        return $counts;
    }

    public function getCategoryBreakdown(): array
    {
        return $this->db->table('requests r')
            ->select('c.name as category_name, COUNT(r.id) as total')
            ->join('categories c', 'c.id = r.category_id', 'left')
            ->groupBy('r.category_id')
            ->get()->getResultArray();
    }
}
