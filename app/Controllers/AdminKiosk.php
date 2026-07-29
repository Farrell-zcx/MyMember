<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class AdminKiosk extends Controller
{
    public function index()
    {
        // Must be logged in as admin
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
        // Must be logged in as admin
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
        // Must be logged in as admin
        if (!session()->get('logged_in')) {
            return $this->response->setStatusCode(401);
        }

        $path = WRITEPATH . 'uploads/live_frame.txt';
        
        if (!file_exists($path) || (time() - filemtime($path)) > 15) {
            return $this->response->setStatusCode(404);
        }

        $frame = file_get_contents($path);

        if ($frame) {
            $data = preg_replace('#^data:image/\w+;base64,#i', '', $frame);
            $binary = base64_decode($data);
            return $this->response
                ->setHeader('Content-Type', 'image/jpeg')
                ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->setHeader('Pragma', 'no-cache')
                ->setBody($binary);
        }

        return $this->response->setStatusCode(404);
    }
}
