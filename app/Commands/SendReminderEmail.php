<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SendReminderEmail extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Email';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'email:reminder';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Send H-2 expiration reminder emails to members.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'email:reminder';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        // Target H+2 dari hari ini (artinya mereka yang akan expired 2 hari lagi)
        $targetDate = date('Y-m-d', strtotime('+2 days'));
        
        $db = \Config\Database::connect();
        $builder = $db->table('members');
        
        // Cari member yg H-2 dan email belum dikirim
        $members = $builder->where('tgl_expired_member', $targetDate)
                           ->where('reminder_terkirim', 0)
                           ->where('email !=', '') // Pastikan punya email
                           ->where('email IS NOT NULL')
                           ->get()
                           ->getResult();

        if (empty($members)) {
            CLI::write('Tidak ada member yang H-2 atau semua email sudah terkirim.', 'yellow');
            return;
        }

        $emailService = \Config\Services::email();
        $countSent = 0;
        $countFailed = 0;

        foreach ($members as $member) {
            $nama = esc($member->nama_lengkap);
            $tglExpired = date('d F Y', strtotime($member->tgl_expired_member));
            
            // Template Email HTML
            $message = "
            <!DOCTYPE html>
            <html>
            <head><meta charset='UTF-8'></head>
            <body style='font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
                    <div style='text-align: center; border-bottom: 2px solid #0056b3; padding-bottom: 15px; margin-bottom: 20px;'>
                        <h1 style='color: #0056b3; margin: 0;'>MyMember</h1>
                        <p style='color: #666; margin: 5px 0 0 0; font-size: 12px;'>Membership Management System</p>
                    </div>
                    <h2 style='color: #333333;'>Halo, {$nama}!</h2>
                    <p style='color: #555555; line-height: 1.6;'>
                        Kami ingin menginformasikan bahwa masa aktif keanggotaan <strong>MyMember</strong> Anda akan segera berakhir dalam <strong>2 hari lagi</strong>.
                    </p>
                    <div style='background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                        <p style='margin: 0; color: #721c24;'>
                            <strong>Tanggal Kadaluarsa:</strong> {$tglExpired}
                        </p>
                    </div>
                    <p style='color: #555555; line-height: 1.6;'>
                        Agar dapat terus menggunakan fasilitas tanpa kendala, silakan lakukan perpanjangan keanggotaan sebelum tanggal kadaluarsa tiba.
                    </p>
                    <div style='text-align: center; margin-top: 30px;'>
                        <p style='font-size: 13px; color: #888888;'>Hubungi admin di tempat untuk informasi perpanjangan paket.</p>
                    </div>
                </div>
            </body>
            </html>";

            $emailService->clear(); // Bersihkan konfigurasi sebelumnya jika ada
            $emailService->setTo($member->email);
            $emailService->setSubject('Pemberitahuan Kadaluarsa Membership (H-2)');
            $emailService->setMessage($message);

            // Jika email sukses terkirim, update status
            if ($emailService->send()) {
                $db->table('members')
                   ->where('NIK', $member->NIK)
                   ->update(['reminder_terkirim' => 1]);
                $countSent++;
                CLI::write("Berhasil mengirim email ke: {$member->email}", 'green');
            } else {
                $countFailed++;
                log_message('error', 'Gagal mengirim email reminder ke: ' . $member->email);
                CLI::write("Gagal mengirim email ke: {$member->email}", 'red');
            }
        }

        CLI::newLine();
        CLI::write("==============================", 'cyan');
        CLI::write("Laporan Pengiriman Email H-2:", 'cyan');
        CLI::write("Berhasil dikirim : {$countSent}", 'green');
        CLI::write("Gagal dikirim    : {$countFailed}", 'red');
        CLI::write("==============================", 'cyan');
    }
}
