<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class LogKunjungan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kunjungan' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'NIK' => [
                'type'       => 'VARCHAR',
                'constraint' => '16',
            ],
            'waktu_kunjungan' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'kuota_awal' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'kuota_akhir' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
        ]);
        $this->forge->addKey('id_kunjungan', true);
        $this->forge->addForeignKey('NIK', 'members', 'NIK', 'CASCADE', 'CASCADE');
        $this->forge->createTable('log_kunjungan');
    }

    public function down()
    {
        $this->forge->dropTable('log_kunjungan');
    }
}