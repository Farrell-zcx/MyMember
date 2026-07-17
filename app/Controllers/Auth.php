<?php

namespace App\Controllers;

use App\Models\AdminModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/admin/dashboard');
        }

        return view('auth/login');
    }

    public function register()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/admin/dashboard');
        }

        return view('auth/register');
    }

    public function loginProcess()
    {
        $session = session();
        $model = new AdminModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $admin = $model->where('username', $username)->first();

        if ($admin) {
            // Verifikasi password hash
            if (password_verify($password, $admin['password'])) {
                $sessionData = [
                    'id_admin'         => $admin['id_admin'],
                    'username'         => $admin['username'],
                    'nama_resepsionis' => $admin['nama_resepsionis'],
                    'logged_in'        => true
                ];
                $session->set($sessionData);
                return redirect()->to('/admin/dashboard');
            } else {
                $session->setFlashdata('msg', 'Password salah, bree!');
                return redirect()->to('/login');
            }
        } else {
            $session->setFlashdata('msg', 'Username tidak ditemukan!');
            return redirect()->to('/login');
        }
    }

    public function registerProcess()
    {
        $session = session();
        $model = new AdminModel();

        $namaResepsionis = $this->request->getPost('nama_resepsionis');
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (empty($namaResepsionis) || empty($username) || empty($password)) {
            $session->setFlashdata('msg', 'Semua kolom pendaftaran harus diisi!');
            return redirect()->to('/register');
        }

        // Cek username duplikat
        $existing = $model->where('username', $username)->first();
        if ($existing) {
            $session->setFlashdata('msg', 'Username sudah terdaftar, bree!');
            return redirect()->to('/register');
        }

        // Simpan admin baru
        $model->save([
            'username'         => $username,
            'password'         => password_hash($password, PASSWORD_DEFAULT),
            'nama_resepsionis' => $namaResepsionis,
        ]);

        $session->setFlashdata('msg', 'Registrasi sukses! Silakan login dengan akun baru.');
        return redirect()->to('/login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}