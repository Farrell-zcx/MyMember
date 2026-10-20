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

            // Bersihkan konfigurasi & attachment sebelumnya
            $emailService->clear(true);

            // Template Email HTML (Table-based layout profesional, anti-spam, 100% responsif)
            $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Pengingat Masa Aktif Membership MyMember</title>
            </head>
            <body style='margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; background-color: #f4f6f9;'>
                <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: #f4f6f9; padding: 30px 10px;'>
                    <tr>
                        <td align='center'>
                            <table border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); overflow: hidden; border: 1px solid #e5e7eb;'>
                                <!-- Brand Header (CSS Table Badge - 0% risiko broken image & 100% lolos filter spam) -->
                                <tr>
                                    <td align='center' style='background-color: #0056b3; padding: 25px 20px;'>
                                        <table border='0' cellpadding='0' cellspacing='0'>
                                            <tr>
                                                <td style='vertical-align: middle; padding-right: 12px;'>
                                                    <div style='background-color: #ffffff; color: #0056b3; font-family: Arial, sans-serif; font-weight: 900; font-size: 22px; width: 44px; height: 44px; line-height: 44px; text-align: center; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);'>M</div>
                                                </td>
                                                <td style='vertical-align: middle;'>
                                                    <div style='color: #ffffff; font-family: Arial, sans-serif; font-size: 24px; font-weight: bold; letter-spacing: 0.5px; line-height: 1.2;'>MyMember</div>
                                                    <div style='color: #cce0ff; font-family: Arial, sans-serif; font-size: 12px; margin-top: 2px;'>Membership Management System</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <!-- Content Body -->
                                <tr>
                                    <td style='padding: 35px 30px; color: #333333;'>
                                        <h2 style='margin-top: 0; margin-bottom: 18px; color: #111827; font-size: 20px; font-weight: 600;'>Halo, {$nama}!</h2>
                                        <p style='color: #4b5563; line-height: 1.6; font-size: 15px; margin-top: 0; margin-bottom: 20px;'>
                                            Kami ingin menginformasikan bahwa masa aktif keanggotaan <strong>MyMember</strong> Anda akan segera berakhir dalam <strong>2 hari lagi</strong>.
                                        </p>
                                        
                                        <!-- Expiration Alert Box -->
                                        <div style='background-color: #fff8e1; border-left: 4px solid #f59e0b; padding: 16px 20px; margin: 25px 0; border-radius: 6px;'>
                                            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                                                <tr>
                                                    <td>
                                                        <p style='margin: 0; color: #b45309; font-size: 15px; font-weight: 600;'>
                                                            Tanggal Kadaluarsa: {$tglExpired}
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>

                                        <p style='color: #4b5563; line-height: 1.6; font-size: 15px; margin-bottom: 25px;'>
                                            Agar dapat terus menikmati fasilitas layanan tanpa kendala, silakan lakukan perpanjangan keanggotaan sebelum tanggal kadaluarsa tiba.
                                        </p>

                                        <div style='background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px 20px; text-align: center; margin-top: 20px;'>
                                            <p style='font-size: 13px; color: #6b7280; margin: 0; line-height: 1.5;'>
                                                Hubungi admin di lokasi untuk informasi perpanjangan paket keanggotaan Anda.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Footer -->
                                <tr>
                                    <td align='center' style='background-color: #f9fafb; padding: 18px 20px; border-top: 1px solid #e5e7eb;'>
                                        <p style='font-size: 12px; color: #9ca3af; margin: 0;'>
                                            &copy; " . date('Y') . " MyMember Official. All rights reserved.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>";

            $altMessage = "Halo, {$nama}!\n\nKami ingin menginformasikan bahwa masa aktif keanggotaan MyMember Anda akan segera berakhir dalam 2 hari lagi.\nTanggal Kadaluarsa: {$tglExpired}\n\nAgar dapat terus menggunakan fasilitas tanpa kendala, silakan lakukan perpanjangan keanggotaan sebelum tanggal kadaluarsa tiba.\n\nHubungi admin di tempat untuk informasi perpanjangan paket.\n\nSalam,\nMyMember Official";

            // Wajib gunakan SMTP_USER (email terautentikasi) agar lolos SPF & DKIM Google 100% dan masuk INBOX
            $smtpUser = config('Email')->SMTPUser ?: (getenv('SMTP_USER') ?: '');
            $fromEmail = !empty($smtpUser) ? $smtpUser : (config('Email')->fromEmail ?: 'labibfarrel960@gmail.com');
            $fromName  = 'MyMember Official';

            $emailService->setFrom($fromEmail, $fromName);
            $emailService->setReplyTo($fromEmail, $fromName);
            $emailService->setTo($member->email);
            $emailService->setSubject("Pengingat Masa Aktif Membership MyMember - {$nama}");
            $emailService->setMessage($message);
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
            }
            sleep(2); // Beri jeda 2 detik agar tidak terkena rate limit Mailtrap (maks 1 email/detik)
        }


        CLI::newLine();
        CLI::write("==============================", 'cyan');
        CLI::write("Laporan Pengiriman Email H-2:", 'cyan');
        CLI::write("Berhasil dikirim : {$countSent}", 'green');
        CLI::write("Gagal dikirim    : {$countFailed}", 'red');
        CLI::write("==============================", 'cyan');
    }
}
