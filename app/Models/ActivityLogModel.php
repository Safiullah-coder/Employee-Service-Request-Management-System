<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table      = 'activity_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['request_id', 'user_id', 'field', 'old_value', 'new_value'];
    protected $useTimestamps = true;

    public function logChange(int $requestId, int $userId, string $field, $oldVal, $newVal): bool
    {
        return (bool) $this->insert([
            'request_id' => $requestId,
            'user_id'    => $userId,
            'field'      => $field,
            'old_value'  => (string)$oldVal,
            'new_value'  => (string)$newVal,
        ]);
    }

    public function getForRequest(int $requestId): array
    {
        return $this->db->table('activity_logs al')
            ->select('al.*, u.name as user_name')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->where('al.request_id', $requestId)
            ->orderBy('al.created_at', 'DESC')
            ->get()->getResultArray();
    }

    public function getAllLogs(int $limit = 100): array
    {
        return $this->db->table('activity_logs al')
            ->select('al.*, u.name as user_name, r.title as request_title')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->join('requests r', 'r.id = al.request_id', 'left')
            ->orderBy('al.created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }
}
