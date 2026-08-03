<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\MemberTypeModel;
use App\Models\MemberModel;
use Config\Services;

class MemberType extends Controller
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $memberModel = new MemberModel();
        
        $data = [
            'nik'                => '',
            'nama_lengkap'       => '',
            'nomor_hp'           => '',
            'email'              => '',
            'id_type'            => '',
            'sisa_kuota'         => '',
            'tgl_expired_member' => '',
            'is_edit'            => false,
            'old_nik'            => '',
            'status'             => $this->request->getGet('status') ?? ''
        ];

        // GET: Ambil data untuk mode edit
        $edit_nik = $this->request->getGet('edit');
        if ($edit_nik) {
            $member = $memberModel->where('NIK', $edit_nik)->first();
            if ($member) {
                $data['nik']                = $member['NIK'];
                $data['nama_lengkap']       = $member['nama_lengkap'];
                $data['nomor_hp']           = $member['nomor_hp'];
                $data['email']              = $member['email'];
                $data['id_type']            = $member['id_type'];
                $data['sisa_kuota']         = $member['sisa_kuota'];
                $data['tgl_expired_member'] = $member['tgl_expired_member'];
                $data['is_edit']            = true;
                $data['old_nik']            = $member['NIK'];
            }
        }

        // GET: Handling hapus data (Soft Delete)
        $delete_nik = $this->request->getGet('delete');
        if ($delete_nik) {
            $memberModel->where('NIK', $delete_nik)->set(['is_deleted' => 1])->update();
            return redirect()->to('/admin/member-type?status=terhapus');
        }

        // POST: Handling tombol simpan & update 
        if ($this->request->getMethod() === 'POST' || $this->request->getMethod() === 'post') {
            $action  = $this->request->getPost('action');
            $old_nik = $this->request->getPost('old_nik');

            // Ambil input form dengan proteksi nilai default jika kosong
            $data_simpan = [
                'NIK'                => $this->request->getPost('nik'),
                'nama_lengkap'       => $this->request->getPost('nama_lengkap'),
                'nomor_hp'           => $this->request->getPost('nomor_hp'),
                'email'              => $this->request->getPost('email'),
                'id_type'            => !empty($this->request->getPost('id_type')) ? intval($this->request->getPost('id_type')) : 2,
                'sisa_kuota'         => !empty($this->request->getPost('sisa_kuota')) ? intval($this->request->getPost('sisa_kuota')) : 0,
                'tgl_expired_member' => !empty($this->request->getPost('tgl_expired_member')) ? $this->request->getPost('tgl_expired_member') : null,
            ];

            if ($action === 'create') {
                $auto_checkin = $this->request->getPost('auto_checkin');

                // Cek apakah NIK sudah ada di database
                $existing = $memberModel->where('NIK', $data_simpan['NIK'])->first();
                if ($existing) {
                    // Lakukan UPSERT/Update jika data sudah ada
                    $nik_target = !empty($old_nik) ? $old_nik : $data_simpan['NIK'];
                    $sukses = $memberModel->updateDataMember($nik_target, $data_simpan, $auto_checkin);
                    
                    if (!$sukses) {
                        return redirect()->to('/admin/member-type?status=gagal_update');
                    }
                    return redirect()->to('/admin/member-type?status=sukses_update');
                }

                // Jika NIK belum ada, lakukan INSERT (Create)
                $sukses = $memberModel->simpanDataBaru($data_simpan);
                if (!$sukses) {
                    return redirect()->to('/admin/member-type?status=gagal_simpan');
                }
                return redirect()->to('/admin/member-type?status=sukses_simpan');
            }

            if ($action === 'update') {
                $auto_checkin = $this->request->getPost('auto_checkin');
                $sukses = $memberModel->updateDataMember($old_nik, $data_simpan, $auto_checkin);
                if (!$sukses) {
                    return redirect()->to('/admin/member-type?status=gagal_update');
                }
                return redirect()->to('/admin/member-type?status=sukses_update');
            }
        }

        $data['members'] = $memberModel->where('is_deleted', 0)->orderBy('created_at', 'DESC')->findAll();
        return view('admin/member_type/index', $data);
    }
    
    public function create()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }
        return view('admin/member_type/create');
    }

    public function store()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $model = new MemberTypeModel();
        $model->insert([
            'type_member'       => $this->request->getPost('type_member'),
            'kuota_kunjungan'   => $this->request->getPost('kuota_kunjungan'),
            'deskripsi_benefit' => $this->request->getPost('deskripsi_benefit'),
        ]);

        return redirect()->to('/admin/member-type');
    }

    public function edit(string $id = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $model = new MemberTypeModel();
        $data['type'] = $model->find($id);

        if (!$data['type']) {
            return redirect()->to('/admin/member-type');
        }

        return view('admin/member_type/edit', $data);
    }

    public function update(string $id = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $model = new MemberTypeModel();
        $model->update($id, [
            'type_member'       => $this->request->getPost('type_member'),
            'kuota_kunjungan'   => $this->request->getPost('kuota_kunjungan'),
            'deskripsi_benefit' => $this->request->getPost('deskripsi_benefit'),
        ]);

        return redirect()->to('/admin/member-type');
    }

    public function delete(string $id = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $model = new MemberTypeModel();
        $model->delete($id);

        return redirect()->to('/admin/member-type');
    }

    public function scanOcr()
    {
        $file = $this->request->getFile('ktp_image');

        if (! $file || ! $file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'pesan' => 'File tidak valid.']);
        }

        // Tembak FastAPI secara internal dari server ke server
        $client = Services::curlrequest();
        try {
            $response = $client->request('POST', 'http://127.0.0.1:8000/extract-ktp', [
                'http_errors' => false,
                'multipart' => [
                    'ktp_image' => new \CURLFile($file->getTempName(), $file->getMimeType(), $file->getName())
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'pesan'  => 'FastAPI returned ' . $response->getStatusCode() . ': ' . $response->getBody()
                ]);
            }

            log_message('error', 'OCR RAW RESPONSE: ' . $response->getBody());
            return $this->response->setJSON(json_decode($response->getBody(), true));
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error', 
                'pesan' => 'Mesin OCR FastAPI offline atau terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function pollScanEvent()
    {
        $cache = \Config\Services::cache();
        $data = $cache->get('latest_ktp_scan');

        if ($data) {
            // Jika ada data, hapus dari cache agar tidak terbaca 2x
            $cache->delete('latest_ktp_scan');
            return $this->response->setJSON($data);
        }

        return $this->response->setJSON(['status' => 'waiting']);
    }
}
