<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Kiosk extends Controller
{
    public function index()
    {
        return view('kiosk/index');
    }

    public function checkTrigger()
    {
        $cache = \Config\Services::cache();
        $isTriggered = $cache->get('kiosk_trigger');
        
        if ($isTriggered === 'yes') {
            $cache->delete('kiosk_trigger');
            return $this->response->setJSON(['trigger' => true]);
        }
        
        return $this->response->setJSON(['trigger' => false]);
    }

    public function streamFrame()
    {
        $image = $this->request->getPost('image');
        if ($image) {
            $path = WRITEPATH . 'uploads/live_frame.txt';
            if (!is_dir(WRITEPATH . 'uploads')) {
                mkdir(WRITEPATH . 'uploads', 0777, true);
            }
            file_put_contents($path, $image);
            return $this->response->setJSON(['status' => 'ok']);
        }
        return $this->response->setJSON(['status' => 'error']);
    }

    public function processOcr()
    {
        $image = $this->request->getPost('image');
        if (!$image) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No image provided']);
        }

        $imageParts = explode(";base64,", $image);
        if (count($imageParts) == 2) {
            $base64Image = $imageParts[1];
        } else {
            $base64Image = $image;
        }

        // Call FastAPI OCR
        try {
            $client = \Config\Services::curlrequest([
                'timeout' => 30, // OCR can take some time
            ]);

            // Save temporary image to send to FastAPI
            $tempFilePath = WRITEPATH . 'uploads/temp_kiosk_' . time() . '.jpg';
            file_put_contents($tempFilePath, base64_decode($base64Image));

            $response = $client->post('http://127.0.0.1:8000/extract-ktp', [
                'multipart' => [
                    'file' => new \CURLFile($tempFilePath, 'image/jpeg', 'ktp.jpg')
                ]
            ]);

            // Delete temp file
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }

            $body = $response->getBody();
            $data = json_decode($body, true);

            if (isset($data['error'])) {
                return $this->response->setJSON(['status' => 'error', 'message' => $data['error']]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'nik' => $data['nik'] ?? null,
                'nama' => $data['nama'] ?? null,
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to connect to OCR Server: ' . $e->getMessage()]);
        }
    }
}
