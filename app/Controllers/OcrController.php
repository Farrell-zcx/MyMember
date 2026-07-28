<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Services;

class OcrController extends BaseController
{
    public function index()
    {
        // Menampilkan halaman upload KTP
        return view('upload_ktp');
    }

    public function scan()
    {
        // Ambil file gambar dari form input bernama 'ktp_image'
        $file = $this->request->getFile('ktp_image');

        // Validasi apakah file valid
        if (! $file->isValid()) {
            return $this->response->setJSON([
                'status' => 'error',
                'pesan'  => $file->getErrorString()
            ]);
        }

        // Inisialisasi HTTP Client bawaan CI 4
        $client = Services::curlrequest();

        try {
            // Kirim file sebagai multipart data ke FastAPI
            $response = $client->request('POST', 'http://127.0.0.1:8000/extract-ktp', [
                'http_errors' => false,
                'multipart' => [
                    'ktp_image' => new \CURLFile($file->getTempName(), $file->getMimeType(), $file->getName())
                ]
            ]);

            // Ambil response body dari FastAPI (berupa string JSON)
            $rawBody = $response->getBody();

            if ($response->getStatusCode() !== 200) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'pesan'  => 'FastAPI returned ' . $response->getStatusCode() . ': ' . $rawBody
                ]);
            }

            $result  = json_decode($rawBody, true);

            // Simpan ke cache jika sukses, agar bisa diambil oleh Admin Panel (Real-time polling)
            if (isset($result['status']) && $result['status'] === 'sukses') {
                $cache = \Config\Services::cache();
                $cache->save('latest_ktp_scan', $result, 60); // Simpan selama 60 detik
            }

            return $this->response->setJSON($result);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'pesan'  => 'Gagal terhubung ke mesin OCR FastAPI: ' . $e->getMessage()
            ]);
        }
    }

    public function checkin()
    {
        $nik = $this->request->getPost('nik');

        if (empty($nik)) {
            return $this->response->setJSON([
                'status' => 'error',
                'pesan'  => 'NIK tidak boleh kosong!'
            ]);
        }

        $db      = \Config\Database::connect();
        $builder = $db->table('members');

        $member = $builder->getWhere(['NIK' => $nik])->getRow();

        if ($member) {
            // Check expiry date
            if (!empty($member->tgl_expired_member) && strtotime($member->tgl_expired_member) < time()) {
                return $this->response->setJSON([
                    'status' => 'expired',
                    'pesan'  => 'Masa aktif member Anda telah habis! Silakan perpanjang di resepsionis.',
                    'data'   => [
                        'nama_lengkap'       => $member->nama_lengkap,
                        'NIK'                => $member->NIK,
                        'sisa_kuota'         => $member->sisa_kuota,
                        'tgl_expired_member' => $member->tgl_expired_member
                    ]
                ]);
            }

            if ($member->sisa_kuota > 0) {
                $newQuota = $member->sisa_kuota - 1;
                $db->table('members')
                    ->where('NIK', $nik)
                    ->update([
                        'sisa_kuota' => $newQuota,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                // Insert ke log_kunjungan
                $db->table('log_kunjungan')->insert([
                    'NIK' => $nik,
                    'waktu_kunjungan' => date('Y-m-d H:i:s'),
                    'kuota_awal' => $member->sisa_kuota,
                    'kuota_akhir' => $newQuota
                ]);

                return $this->response->setJSON([
                    'status'     => 'sukses',
                    'pesan'      => 'Check-in berhasil! Selamat datang!',
                    'nama'       => $member->nama_lengkap,
                    'sisa_kuota' => $newQuota
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'limit',
                    'pesan'  => 'Kuota kunjungan Anda sudah habis! Silakan lakukan isi ulang di resepsionis.',
                    'data'   => [
                        'nama_lengkap'       => $member->nama_lengkap,
                        'NIK'                => $member->NIK,
                        'sisa_kuota'         => $member->sisa_kuota,
                        'tgl_expired_member' => $member->tgl_expired_member
                    ]
                ]);
            }
        } else {
            return $this->response->setJSON([
                'status' => 'unregistered',
                'pesan'  => 'NIK Anda belum terdaftar sebagai member! Silakan mendaftar di meja resepsionis.'
            ]);
        }
    }

    public function getMemberByNik()
    {
        $nik = $this->request->getGet('nik');

        if (empty($nik)) {
            return $this->response->setJSON([
                'status' => 'error',
                'pesan'  => 'NIK tidak boleh kosong!'
            ]);
        }

        $db      = \Config\Database::connect();
        $builder = $db->table('members');
        $builder->select('members.*, master_type_member.type_member');
        $builder->join('master_type_member', 'master_type_member.id_type = members.id_type', 'left');
        $builder->where('members.NIK', $nik);
        $member  = $builder->get()->getRow();

        if ($member) {
            return $this->response->setJSON([
                'status' => 'sukses',
                'exists' => true,
                'data'   => [
                    'nama_lengkap'       => $member->nama_lengkap,
                    'nomor_hp'           => $member->nomor_hp,
                    'email'              => $member->email,
                    'id_type'            => $member->id_type,
                    'type_member'        => $member->type_member ?? '-',
                    'sisa_kuota'         => $member->sisa_kuota,
                    'tgl_expired_member' => $member->tgl_expired_member
                ]
            ]);
        }

        return $this->response->setJSON([
            'status' => 'sukses',
            'exists' => false
        ]);
    }

    public function updateCache()
    {
        $nik = $this->request->getPost('nik');
        $nama = $this->request->getPost('nama');

        if (empty($nik) || empty($nama)) {
            return $this->response->setJSON([
                'status' => 'error',
                'pesan'  => 'Data NIK dan Nama tidak lengkap'
            ]);
        }

        $result = [
            'status' => 'sukses',
            'data_ktp' => [
                'nik'  => $nik,
                'nama' => $nama
            ]
        ];

        $cache = \Config\Services::cache();
        $cache->save('latest_ktp_scan', $result, 60);

        return $this->response->setJSON([
            'status' => 'sukses',
            'pesan'  => 'Cache diperbarui'
        ]);
    }
}