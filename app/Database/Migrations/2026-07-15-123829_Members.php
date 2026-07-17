<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Members extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'NIK' => [
                'type'       => 'VARCHAR',
                'constraint' => '16',
            ],
            'nama_lengkap' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'nomor_hp' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'id_type' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'sisa_kuota' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'tgl_expired_member' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'reminder_terkirim' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
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
        $this->forge->addKey('NIK', true);
        $this->forge->addForeignKey('id_type', 'master_type_member', 'id_type', 'CASCADE', 'CASCADE');
        $this->forge->createTable('members');
    }

    public function down()
    {
        $this->forge->dropTable('members');
    }
}