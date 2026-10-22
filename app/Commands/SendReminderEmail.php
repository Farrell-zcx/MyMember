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

        // Ambil konfigurasi SMTP resmi Google Mail
        $smtpHost   = getenv('SMTP_HOST') ?: (config('Email')->SMTPHost ?: 'smtp.gmail.com');
        $smtpUser   = getenv('SMTP_USER') ?: (config('Email')->SMTPUser ?: 'labibfarrel960@gmail.com');
        $smtpPass   = getenv('SMTP_PASS') ?: (config('Email')->SMTPPass ?: '');
        $smtpPort   = (int) (getenv('SMTP_PORT') ?: (config('Email')->SMTPPort ?: 587));
        $smtpCrypto = getenv('SMTP_CRYPTO') ?: (config('Email')->SMTPCrypto ?: 'tls');
        $fromEmail  = $smtpUser;
        $fromName   = getenv('SMTP_FROM_NAME') ?: (config('Email')->fromName ?: 'Farrel Labib - MyMember');

        $smtpConfig = [
            'protocol'       => 'smtp',
            'SMTPHost'       => $smtpHost,
            'SMTPUser'       => $smtpUser,
            'SMTPPass'       => $smtpPass,
            'SMTPPort'       => $smtpPort,
            'SMTPCrypto'     => $smtpCrypto,
            'SMTPAuthMethod' => 'login',
            'mailType'       => 'html',
            'charset'        => 'UTF-8',
            'newline'        => "\r\n",
            'CRLF'           => "\r\n",
            'userAgent'      => '',
        ];

        CLI::write("[SMTP] Menggunakan server: {$smtpHost}:{$smtpPort} (Akun: {$smtpUser})", 'cyan');

        foreach ($members as $member) {
            $nama = esc($member->nama_lengkap);
            $tglExpired = date('d F Y', strtotime($member->tgl_expired_member));

            // Bersihkan konfigurasi pesan sebelumnya & inisialisasi ulang SMTP
            $emailService->clear(true);
            $emailService->initialize($smtpConfig);

            // Render view email template dengan header branding asli MyMember
            $emailMessage = view('email/reminder', [
                'nama'       => $nama,
                'tglExpired' => $tglExpired,
            ]);

            $altMessage = "Halo {$nama},\n\nKami menginformasikan bahwa masa aktif keanggotaan MyMember Anda tercatat aktif hingga: {$tglExpired}.\n\nAgar dapat terus menikmati fasilitas layanan tanpa kendala, silakan lakukan perpanjangan sebelum tanggal tersebut tiba.\n\nHubungi admin di lokasi untuk informasi status atau perpanjangan paket keanggotaan Anda.\n\nSalam,\nMyMember Official";

            $emailService->setFrom($fromEmail, $fromName);
            $emailService->setTo($member->email);
            $emailService->setSubject('[MyMember] Informasi Masa Aktif Keanggotaan Anda');
            $emailService->setMessage($emailMessage);
            $emailService->setAltMessage($altMessage);

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
                CLI::write($emailService->printDebugger(['headers']), 'red');
            }
            sleep(2); // Beri jeda 2 detik agar tidak terkena rate limit
        }


        CLI::newLine();
        CLI::write("==============================", 'cyan');
        CLI::write("Laporan Pengiriman Email H-2:", 'cyan');
        CLI::write("Berhasil dikirim : {$countSent}", 'green');
        CLI::write("Gagal dikirim    : {$countFailed}", 'red');
        CLI::write("==============================", 'cyan');
    }
}
