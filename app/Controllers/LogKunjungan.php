<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class LogKunjungan extends Controller
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $db      = \Config\Database::connect();
        $builder = $db->table('log_kunjungan');
        $builder->select('log_kunjungan.*, members.nama_lengkap');
        $builder->join('members', 'members.NIK = log_kunjungan.NIK', 'left');
        $builder->orderBy('log_kunjungan.waktu_kunjungan', 'DESC');

        $data['logs'] = $builder->get()->getResultArray();

        return view('admin/log_kunjungan', $data);
    }
}
