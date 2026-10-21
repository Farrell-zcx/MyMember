<?php
/**
 * @var string $nama
 * @var string $tglExpired
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pengingat Masa Aktif Membership MyMember</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f7f6; padding: 40px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden;">
                    
                    <!-- Header (Identik dengan SSO Engine) -->
                    <tr>
                        <td align="center" style="background-color: #2563eb; padding: 30px 20px;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">MyMember</h1>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px; color: #333333;">
                            <h2 style="margin-top: 0; font-size: 20px; font-weight: 600; color: #111827;">Halo <?= esc($nama) ?>,</h2>
                            <p style="font-size: 15px; line-height: 1.6; color: #4b5563; margin-bottom: 25px;">
                                Kami ingin menginformasikan bahwa masa aktif keanggotaan <strong>MyMember</strong> Anda akan segera berakhir dalam <strong>2 hari lagi</strong>.
                            </p>
                            
                            <div style="background-color: #f3f4f6; border-left: 4px solid #2563eb; padding: 15px 20px; margin: 25px 0; border-radius: 4px;">
                                <p style="margin: 0; font-size: 15px; color: #1f2937; font-weight: 600;">
                                    Tanggal Kadaluarsa: <?= esc($tglExpired) ?>
                                </p>
                            </div>

                            <p style="font-size: 14px; line-height: 1.5; color: #4b5563; margin-bottom: 15px;">
                                Agar dapat terus menggunakan fasilitas tanpa kendala, silakan lakukan perpanjangan keanggotaan sebelum tanggal kadaluarsa tiba.
                            </p>
                            
                            <p style="font-size: 13px; line-height: 1.5; color: #6b7280; margin-top: 30px;">
                                Hubungi admin di lokasi untuk informasi perpanjangan paket keanggotaan Anda.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background-color: #f9fafb; padding: 20px; border-top: 1px solid #e5e7eb;">
                            <p style="font-size: 12px; color: #9ca3af; margin: 0;">
                                &copy; <?= date('Y') ?> MyMember System. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
