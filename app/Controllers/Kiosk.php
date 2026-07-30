<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Kiosk extends Controller
{
    public function index()
    {
        // Redirect ke halaman kiosk baru (upload_ktp.php)
        return redirect()->to('/ocr');
    }

    public function checkTrigger()
    {
        $cache = \Config\Services::cache();
        $isTriggered = $cache->get('kiosk_trigger');
        
        $response = $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache');

        if ($isTriggered === 'yes') {
            $cache->delete('kiosk_trigger');
            return $response->setJSON(['trigger' => true]);
        }
        
        return $response->setJSON(['trigger' => false]);
    }

    public function streamFrame()
    {
        $file = $this->request->getFile('image');
        if ($file && $file->isValid()) {
            if (!is_dir(WRITEPATH . 'uploads')) {
                mkdir(WRITEPATH . 'uploads', 0777, true);
            }
            // Overwrite the same image file
            $file->move(WRITEPATH . 'uploads', 'live_frame.jpg', true);
            return $this->response->setJSON(['status' => 'ok']);
        }
        $error = $file ? $file->getErrorString() : 'No file';
        return $this->response->setJSON(['status' => 'error', 'msg' => $error, 'files' => $_FILES, 'post' => $_POST]);
    }
}
