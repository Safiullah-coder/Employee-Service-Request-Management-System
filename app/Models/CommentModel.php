<?php

namespace App\Models;

use CodeIgniter\Model;

class CommentModel extends Model
{
    protected $table      = 'comments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['request_id', 'user_id', 'comment'];
    protected $useTimestamps = true;

    public function getForRequest(int $requestId): array
    {
        return $this->db->table('comments c')
            ->select('c.*, u.name as user_name, u.role as user_role')
            ->join('users u', 'u.id = c.user_id', 'left')
            ->where('c.request_id', $requestId)
            ->orderBy('c.created_at', 'ASC')
            ->get()->getResultArray();
    }
}
