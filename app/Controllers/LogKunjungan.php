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

    public function getLiveLogs()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $db      = \Config\Database::connect();
        $builder = $db->table('log_kunjungan');
        $builder->select('log_kunjungan.*, members.nama_lengkap');
        $builder->join('members', 'members.NIK = log_kunjungan.NIK', 'left');
        $builder->orderBy('log_kunjungan.waktu_kunjungan', 'DESC');

        $logs = $builder->get()->getResultArray();

        // Format tanggal agar sama persis dengan backend index() 
        foreach ($logs as &$log) {
            $log['waktu_format'] = date('d M Y H:i:s', strtotime($log['waktu_kunjungan']));
        }

        return $this->response->setJSON([
            'status' => 'sukses',
            'data' => $logs
        ]);
    }
}
