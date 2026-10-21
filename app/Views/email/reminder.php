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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat Masa Aktif Membership MyMember</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f6f9;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f6f9; padding: 30px 10px;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); padding: 30px; border: 1px solid #e5e7eb;">
                    
                    <!-- Header Branding Asli MyMember -->
                    <tr>
                        <td align="center" style="border-bottom: 2px solid #0056b3; padding-bottom: 15px; margin-bottom: 20px;">
                            <div style="margin-bottom: 5px;">
                                <img src="https://mymember.tech/images/logo.png" alt="MyMember Logo" width="40" height="40" style="height: 40px; width: auto; vertical-align: middle; margin-right: 8px; border: 0;">
                                <h1 style="margin: 0; font-size: 28px; color: #333333; display: inline-block; vertical-align: middle;"><span style="color: #0056b3;">My</span>Member</h1>
                            </div>
                            <p style="color: #666666; margin: 0; font-size: 12px;">Membership Management System</p>
                        </td>
                    </tr>
                    
                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 25px 10px 15px 10px; color: #333333;">
                            <h2 style="margin-top: 0; color: #333333; font-size: 20px;">Halo, <?= esc($nama) ?>!</h2>
                            <p style="color: #555555; line-height: 1.6; font-size: 15px;">
                                Kami ingin menginformasikan bahwa masa aktif keanggotaan <strong>MyMember</strong> Anda akan segera berakhir dalam <strong>2 hari lagi</strong>.
                            </p>
                            
                            <!-- Expiration Alert Box -->
                            <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #721c24; font-size: 15px;">
                                    <strong>Tanggal Kadaluarsa:</strong> <?= esc($tglExpired) ?>
                                </p>
                            </div>

                            <p style="color: #555555; line-height: 1.6; font-size: 15px;">
                                Agar dapat terus menggunakan fasilitas tanpa kendala, silakan lakukan perpanjangan keanggotaan sebelum tanggal kadaluarsa tiba.
                            </p>
                            
                            <div style="text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #eeeeee;">
                                <p style="font-size: 13px; color: #888888; margin: 0;">Hubungi admin di tempat untuk informasi perpanjangan paket.</p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding-top: 15px;">
                            <p style="font-size: 11px; color: #aaaaaa; margin: 0;">
                                &copy; <?= date('Y') ?> MyMember Official. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
