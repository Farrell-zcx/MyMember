<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberModel extends Model
{
    protected $table            = 'members';
    protected $primaryKey       = 'NIK';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'NIK', 'nama_lengkap', 'nomor_hp', 'email', 'id_type', 
        'sisa_kuota', 'tgl_expired_member', 'reminder_terkirim', 'is_deleted'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Menyimpan data member baru sekaligus mencatat log check-in perdana
     */
    public function simpanDataBaru($data_simpan)
    {
        $db = \Config\Database::connect();
        
        // Ambil data kuota master dan masa aktif berdasarkan id_type
        $masterType = $db->table('master_type_member')->where('id_type', $data_simpan['id_type'])->get()->getRow();
        
        $kuota_master = $masterType && isset($masterType->kuota_kunjungan) ? (int)$masterType->kuota_kunjungan : 0;
        $masa_aktif_hari = $masterType && isset($masterType->masa_aktif_hari) ? (int)$masterType->masa_aktif_hari : 0;
        
        // Jika input tanggal kedaluwarsa kosong, set otomatis berdasarkan master (dihitung dari tanggal hari ini)
        if (empty($data_simpan['tgl_expired_member']) && $masa_aktif_hari > 0) {
            $data_simpan['tgl_expired_member'] = date('Y-m-d', strtotime("+$masa_aktif_hari days"));
        }
        
        // Jika input sisa_kuota kosong/0, gunakan kuota dari master
        if (empty($data_simpan['sisa_kuota']) || $data_simpan['sisa_kuota'] == 0) {
            $kuota_awal = $kuota_master;
        } else {
            $kuota_awal = (int)$data_simpan['sisa_kuota'];
        }
        
        // Potong 1 kuota untuk check-in pertama
        $kuota_akhir = $kuota_awal > 0 ? $kuota_awal - 1 : 0;
        $data_simpan['sisa_kuota'] = $kuota_akhir;

        $db->transStart();
        
        // Insert ke tabel members
        $this->insert($data_simpan);
        
        // Insert ke log_kunjungan sebagai check-in perdana
        $db->table('log_kunjungan')->insert([
            'NIK'             => $data_simpan['NIK'],
            'waktu_kunjungan' => date('Y-m-d H:i:s'),
            'kuota_awal'      => $kuota_awal,
            'kuota_akhir'     => $kuota_akhir
        ]);
        
        $db->transComplete();

        return $db->transStatus();
    }

    /**
     * Update data member dan lakukan auto check-in jika dicentang
     */
    public function updateDataMember($old_nik, $data_simpan, $auto_checkin)
    {
        $db = \Config\Database::connect();
        $data_simpan['reminder_terkirim'] = 0; // Reset status email reminder
        
        $db->transStart();
        if ($auto_checkin == '1' && $data_simpan['sisa_kuota'] > 0) {
            $kuota_awal = $data_simpan['sisa_kuota'];
            $data_simpan['sisa_kuota'] = $kuota_awal - 1;
            
            $this->where('NIK', $old_nik)->set($data_simpan)->update();
            
            // Insert ke log_kunjungan
            $db->table('log_kunjungan')->insert([
                'NIK'             => $old_nik, 
                'waktu_kunjungan' => date('Y-m-d H:i:s'),
                'kuota_awal'      => $kuota_awal,
                'kuota_akhir'     => $data_simpan['sisa_kuota']
            ]);
        } else {
            $this->where('NIK', $old_nik)->set($data_simpan)->update();
        }
        $db->transComplete();
        
        return $db->transStatus();
    }
}
