<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        // Total member aktif 
        $total_members = $db->table('members')->countAllResults();

        // Total pengunjung hari ini
        // DATE(waktu_kunjungan) = CURDATE()
        $total_kunjungan_hari_ini = $db->table('log_kunjungan')
            ->where('DATE(waktu_kunjungan)', date('Y-m-d'))
            ->countAllResults();

        // Daftar kunjungan hari ini terbaru
        $builder = $db->table('log_kunjungan');
        $builder->select('log_kunjungan.*, members.nama_lengkap, members.sisa_kuota as sisa_kuota_terbaru');
        $builder->join('members', 'members.NIK = log_kunjungan.NIK', 'left');
        $builder->where('DATE(log_kunjungan.waktu_kunjungan)', date('Y-m-d'));
        $builder->orderBy('log_kunjungan.waktu_kunjungan', 'DESC');
        $builder->limit(10);
        $recent_logs = $builder->get()->getResultArray();

        $data = [
            'total_members' => $total_members,
            'total_kunjungan_hari_ini' => $total_kunjungan_hari_ini,
            'recent_logs' => $recent_logs
        ];

        return view('admin/dashboard', $data);
    }

    public function getLiveStats()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $db = \Config\Database::connect();

        $total_members = $db->table('members')->countAllResults();

        $total_kunjungan_hari_ini = $db->table('log_kunjungan')
            ->where('DATE(waktu_kunjungan)', date('Y-m-d'))
            ->countAllResults();

        $builder = $db->table('log_kunjungan');
        $builder->select('log_kunjungan.*, members.nama_lengkap, members.sisa_kuota as sisa_kuota_terbaru');
        $builder->join('members', 'members.NIK = log_kunjungan.NIK', 'left');
        $builder->where('DATE(log_kunjungan.waktu_kunjungan)', date('Y-m-d'));
        $builder->orderBy('log_kunjungan.waktu_kunjungan', 'DESC');
        $builder->limit(10);
        $recent_logs = $builder->get()->getResultArray();

        // Format time to H:i (ubah waktu WIB)
        foreach ($recent_logs as &$log) {
            $log['jam'] = date('H:i', strtotime($log['waktu_kunjungan']));
        }

        return $this->response->setJSON([
            'total_members' => $total_members,
            'total_kunjungan_hari_ini' => $total_kunjungan_hari_ini,
            'recent_logs' => $recent_logs
        ]);
    }
}
