<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\MemberTypeModel;
use Config\Services;

class MemberType extends Controller
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $db      = \Config\Database::connect();
        $builder = $db->table('members');

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
            $member = $builder->getWhere(['NIK' => $edit_nik])->getRow();
            if ($member) {
                $data['nik']                = $member->NIK;
                $data['nama_lengkap']       = $member->nama_lengkap;
                $data['nomor_hp']           = $member->nomor_hp;
                $data['email']              = $member->email;
                $data['id_type']            = $member->id_type;
                $data['sisa_kuota']         = $member->sisa_kuota;
                $data['tgl_expired_member'] = $member->tgl_expired_member;
                $data['is_edit']            = true;
                $data['old_nik']            = $member->NIK;
            }
        }

        // GET: Handling hapus data
        $delete_nik = $this->request->getGet('delete');
        if ($delete_nik) {
            $builder->where('NIK', $delete_nik)->delete();
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

                // Jika ID Type kosong, isi angka 1 (sesuaikan ID master)
                'id_type' => !empty($this->request->getPost('id_type')) ? intval($this->request->getPost('id_type')) : 2,

                'sisa_kuota'         => !empty($this->request->getPost('sisa_kuota')) ? intval($this->request->getPost('sisa_kuota')) : 0,
                'tgl_expired_member' => !empty($this->request->getPost('tgl_expired_member')) ? $this->request->getPost('tgl_expired_member') : null,
                'updated_at'         => date('Y-m-d H:i:s')
            ];

            if ($action === 'create') {
                $data_simpan['created_at'] = date('Y-m-d H:i:s');
                // Auto-decrement quota by 1 upon receptionist registration (first visit check-in)
                if ($data_simpan['sisa_kuota'] > 0) {
                    $data_simpan['sisa_kuota'] = $data_simpan['sisa_kuota'] - 1;
                }
                $builder->insert($data_simpan);
                return redirect()->to('/admin/member-type?status=sukses_simpan');
            }

            if ($action === 'update') {
                $builder->where('NIK', $old_nik)->update($data_simpan);
                return redirect()->to('/admin/member-type?status=sukses_update');
            }
        }

        $data['members'] = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();
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

    public function edit($id)
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

    public function update($id)
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

    public function delete($id)
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

            return $this->response->setJSON(json_decode($response->getBody(), true));
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error', 
                'pesan' => 'Mesin OCR FastAPI offline atau terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
}
