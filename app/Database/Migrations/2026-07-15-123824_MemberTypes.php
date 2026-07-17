<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MemberTypes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_type' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'type_member' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'kuota_kunjungan' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'deskripsi_benefit' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_type', true);
        $this->forge->createTable('master_type_member');
    }

    public function down()
    {
        $this->forge->dropTable('master_type_member');
    }
}