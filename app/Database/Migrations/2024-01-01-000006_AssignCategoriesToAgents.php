<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AssignCategoriesToAgents extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'category_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'role'],
        ]);
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'SET NULL');
        $this->forge->processIndexes('users');
        $this->db->query(
            "UPDATE users SET category_id = (SELECT id FROM categories WHERE name = 'IT' LIMIT 1) WHERE email = 'it@company.com'"
        );
        $this->db->query(
            "UPDATE users SET category_id = (SELECT id FROM categories WHERE name = 'HR' LIMIT 1) WHERE email = 'hr@company.com'"
        );
        $this->db->query(
            "UPDATE users SET category_id = (SELECT id FROM categories WHERE name = 'Facility' LIMIT 1) WHERE email = 'facility@company.com'"
        );
    }

    public function down()
    {
        $this->forge->dropForeignKey('users', 'users_category_id_foreign');
        $this->forge->dropColumn('users', 'category_id');
    }
}
