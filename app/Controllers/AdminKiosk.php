<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class AdminKiosk extends Controller
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Admin - Kiosk Controller',
        ];
        return view('admin/kiosk', $data);
    }

    public function trigger()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        // Set cache trigger for 10 seconds 
        $cache = \Config\Services::cache();
        $cache->save('kiosk_trigger', 'yes', 10);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Sinyal berhasil dikirim ke Kiosk']);
    }

    public function getStreamFrame()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setStatusCode(401);
        }

        $path = WRITEPATH . 'uploads/live_frame.jpg';
        
        if (!file_exists($path) || (time() - filemtime($path)) > 30) {
            return $this->response->setStatusCode(404);
        }

        $binary = file_get_contents($path);

        if ($binary) {
            return $this->response
                ->setHeader('Content-Type', 'image/jpeg')
                ->setHeader('Cache-Control', 'no-cache, must-revalidate')
                ->setBody($binary);
        }

        return $this->response->setStatusCode(404);
    }
}
