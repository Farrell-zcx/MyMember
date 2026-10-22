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
    <title>Informasi Masa Aktif Membership MyMember</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f6f9;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f6f9; padding: 35px 15px;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e5e7eb;">
                    
                    <!-- Header Branding MyMember (CDN Global Cloudflare: 0% attachment & 100% selalu muncul di header) -->
                    <tr>
                        <td align="center" style="background-color: #ffffff; border-bottom: 2px solid #0056b3; padding: 25px 20px 20px 20px;">
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align: middle; padding-right: 12px;">
                                        <img src="https://cdn.jsdelivr.net/gh/Farrell-zcx/MyMember@feat/vps-deployment/public/images/logo.png" alt="MyMember" width="40" height="40" style="display: block; width: 40px; height: 40px; border: 0;">
                                    </td>
                                    <td style="vertical-align: middle; text-align: left;">
                                        <h1 style="margin: 0; font-size: 26px; font-weight: bold; color: #333333; line-height: 1.2;">
                                            <span style="color: #0056b3;">My</span>Member
                                        </h1>
                                        <p style="color: #666666; margin: 2px 0 0 0; font-size: 12px;">Membership Management System</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 30px 25px 20px 25px; color: #333333;">
                            <h2 style="margin-top: 0; margin-bottom: 15px; color: #111827; font-size: 19px; font-weight: 600;">Halo, <?= esc($nama) ?>!</h2>
                            <p style="color: #4b5563; line-height: 1.6; font-size: 15px; margin-top: 0; margin-bottom: 20px;">
                                Kami menginformasikan status masa aktif keanggotaan <strong>MyMember</strong> Anda saat ini:
                            </p>
                            
                            <!-- Membership Info Box -->
                            <div style="background-color: #eff6ff; border-left: 4px solid #2563eb; padding: 15px 20px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #1e40af; font-size: 15px; font-weight: 600;">
                                    Masa Aktif Tercatat Hingga: <?= esc($tglExpired) ?>
                                </p>
                            </div>

                            <p style="color: #4b5563; line-height: 1.6; font-size: 15px; margin-bottom: 25px;">
                                Agar dapat terus menikmati seluruh fasilitas layanan tanpa kendala, Anda dapat melakukan perpanjangan paket keanggotaan sebelum tanggal tersebut tiba.
                            </p>
                            
                            <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px 20px; text-align: center; margin-top: 25px;">
                                <p style="font-size: 13px; color: #6b7280; margin: 0; line-height: 1.5;">
                                    Hubungi admin atau customer service di lokasi untuk informasi status atau perpanjangan paket keanggotaan Anda.
                                </p>
                            </div>
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
