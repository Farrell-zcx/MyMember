<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'username'         => 'admin',
            'password'         => password_hash('admin123', PASSWORD_DEFAULT),
            'nama_resepsionis' => 'Farrel Alhaidar', 
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        // Insert ke tabel admin
        $this->db->table('admin')->insert($data);
    }
}