<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;

class LogKunjungan extends Controller
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $db      = \Config\Database::connect();
        $builder = $db->table('log_kunjungan');
        $builder->select('log_kunjungan.*, members.nama_lengkap, master_type_member.type_member');
        $builder->join('members', 'members.NIK = log_kunjungan.NIK', 'left');
        $builder->join('master_type_member', 'master_type_member.id_type = members.id_type', 'left');
        
        if (!empty($startDate) && !empty($endDate)) {
            $builder->where('DATE(log_kunjungan.waktu_kunjungan) >=', $startDate);
            $builder->where('DATE(log_kunjungan.waktu_kunjungan) <=', $endDate);
        }

        $builder->orderBy('log_kunjungan.waktu_kunjungan', 'DESC');

        $data['logs'] = $builder->get()->getResultArray();
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;

        return view('admin/log_kunjungan', $data);
    }

    public function getLiveLogs()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $db      = \Config\Database::connect();
        $builder = $db->table('log_kunjungan');
        $builder->select('log_kunjungan.*, members.nama_lengkap, master_type_member.type_member');
        $builder->join('members', 'members.NIK = log_kunjungan.NIK', 'left');
        $builder->join('master_type_member', 'master_type_member.id_type = members.id_type', 'left');
        
        if (!empty($startDate) && !empty($endDate)) {
            $builder->where('DATE(log_kunjungan.waktu_kunjungan) >=', $startDate);
            $builder->where('DATE(log_kunjungan.waktu_kunjungan) <=', $endDate);
        }

        $builder->orderBy('log_kunjungan.waktu_kunjungan', 'DESC');

        $logs = $builder->get()->getResultArray();

        // Format tanggal agar sama persis dengan backend index() 
        foreach ($logs as &$log) {
            $log['waktu_format'] = date('d M Y H:i:s', strtotime($log['waktu_kunjungan']));
        }

        return $this->response->setJSON([
            'status' => 'sukses',
            'data' => $logs
        ]);
    }

    private function getLogData($startDate, $endDate)
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('log_kunjungan');
        $builder->select('log_kunjungan.*, members.nama_lengkap, master_type_member.type_member');
        $builder->join('members', 'members.NIK = log_kunjungan.NIK', 'left');
        $builder->join('master_type_member', 'master_type_member.id_type = members.id_type', 'left');
        
        if (!empty($startDate) && !empty($endDate)) {
            $builder->where('DATE(log_kunjungan.waktu_kunjungan) >=', $startDate);
            $builder->where('DATE(log_kunjungan.waktu_kunjungan) <=', $endDate);
        }

        $builder->orderBy('log_kunjungan.waktu_kunjungan', 'DESC');
        return $builder->get()->getResultArray();
    }

    public function exportExcel()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $logs = $this->getLogData($startDate, $endDate);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $sheet->setCellValue('A1', 'Histori Kunjungan');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $periode = 'Periode: ' . (!empty($startDate) && !empty($endDate) ? "$startDate s/d $endDate" : 'Semua Waktu');
        $sheet->setCellValue('A2', $periode);
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Column headers
        $sheet->setCellValue('A4', 'Waktu Check-in');
        $sheet->setCellValue('B4', 'NIK');
        $sheet->setCellValue('C4', 'Nama Lengkap');
        $sheet->setCellValue('D4', 'Tipe Member');
        $sheet->setCellValue('E4', 'Kuota Awal');
        $sheet->setCellValue('F4', 'Sisa Kuota');

        // Styling the headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'E0E0E0'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ];
        $sheet->getStyle('A4:F4')->applyFromArray($headerStyle);

        // Populate data
        $row = 5;
        foreach ($logs as $log) {
            $sheet->setCellValue('A' . $row, date('d M Y H:i:s', strtotime($log['waktu_kunjungan'])));
            $sheet->setCellValueExplicit('B' . $row, $log['NIK'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $log['nama_lengkap'] ?? 'Tidak Diketahui');
            $sheet->setCellValue('D' . $row, $log['type_member'] ?? '-');
            $sheet->setCellValue('E' . $row, $log['kuota_awal']);
            $sheet->setCellValue('F' . $row, $log['kuota_akhir']);
            
            // Add borders to data row
            $sheet->getStyle('A'.$row.':F'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Histori_Kunjungan_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    public function exportPdf()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $data['logs'] = $this->getLogData($startDate, $endDate);
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;

        $html = view('admin/export/pdf_log_kunjungan', $data);

        $mpdf = new Mpdf([
            'mode' => 'utf-8', 
            'format' => 'A4',
            'orientation' => 'P',
            'margin_top' => 45, 
            'margin_bottom' => 20,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_header' => 10,
        ]);
        
        $mpdf->setAutoTopMargin = 'stretch';
        
        $mpdf->SetTitle('Export Histori Kunjungan');
        $mpdf->WriteHTML($html);
        
        $filename = 'Histori_Kunjungan_' . date('Ymd_His') . '.pdf';
        
        $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        exit;
    }
}
