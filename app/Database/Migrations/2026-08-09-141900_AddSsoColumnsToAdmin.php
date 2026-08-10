<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSsoColumnsToAdmin extends Migration
{
    public function up()
    {
        $this->forge->addColumn('admin', [
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'nama_resepsionis',
            ],
            'sso_user_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
                'null'       => true,
                'after'      => 'email',
            ],
            'synced_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'sso_user_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('admin', ['email', 'sso_user_id', 'synced_at']);
    }
}
