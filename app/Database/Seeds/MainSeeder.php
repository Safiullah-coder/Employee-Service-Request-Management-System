<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MainSeeder extends Seeder
{
    public function run()
    {
        // Categories with SLA hours
        $categories = [
            ['name' => 'IT',       'sla_hours' => 24, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'HR',       'sla_hours' => 48, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Facility', 'sla_hours' => 72, 'created_at' => date('Y-m-d H:i:s')],
        ];
        $this->db->table('categories')->insertBatch($categories);

        $categoryIds = [];
        foreach ($this->db->table('categories')->get()->getResultArray() as $category) {
            $categoryIds[$category['name']] = $category['id'];
        }

        // Demo users: one agent for each service category.
        $password = password_hash('password123', PASSWORD_DEFAULT);
        $users = [
            ['name' => 'Super Admin',    'email' => 'admin@company.com',    'password' => $password, 'role' => 'admin',    'category_id' => null,                   'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'IT Agent',       'email' => 'it@company.com',       'password' => $password, 'role' => 'agent',    'category_id' => $categoryIds['IT'],    'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'HR Agent',       'email' => 'hr@company.com',       'password' => $password, 'role' => 'agent',    'category_id' => $categoryIds['HR'],    'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Facilities Agent', 'email' => 'facility@company.com', 'password' => $password, 'role' => 'agent',  'category_id' => $categoryIds['Facility'], 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Ali Employee',   'email' => 'ali@company.com',      'password' => $password, 'role' => 'employee', 'category_id' => null,                   'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Sara Employee',  'email' => 'sara@company.com',     'password' => $password, 'role' => 'employee', 'category_id' => null,                   'created_at' => date('Y-m-d H:i:s')],
        ];
        $this->db->table('users')->insertBatch($users);
    }
}
