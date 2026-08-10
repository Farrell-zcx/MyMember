<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use PDO;

class SsoSyncCommand extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'SSO';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'sso:sync';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Sinkronisasi admin lokal MyMember ke SSO Engine';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'sso:sync';

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        CLI::write('Memulai sinkronisasi Admin MyMember ke SSO Engine...', 'yellow');

        // Koneksi ke database MyMember
        $dbMyMember = \Config\Database::connect('default');

        // Koneksi PDO manual ke database SSO Engine
        // Karena di Laragon biasanya root tanpa password, kita asumsikan default
        $ssoHost = '127.0.0.1';
        $ssoUser = 'root';
        $ssoPass = '';
        $ssoDb   = 'sso_engine';

        try {
            $pdoSso = new PDO("mysql:host=$ssoHost;dbname=$ssoDb;charset=utf8mb4", $ssoUser, $ssoPass);
            $pdoSso->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            CLI::error("Koneksi ke SSO Engine gagal: " . $e->getMessage());
            return;
        }

        // Ambil admin yang sso_user_id nya masih kosong
        $admins = $dbMyMember->table('admin')->where('sso_user_id', null)->get()->getResultArray();

        if (empty($admins)) {
            CLI::write('Tidak ada admin yang perlu disinkronisasi.', 'green');
            return;
        }

        CLI::write('Ditemukan ' . count($admins) . ' admin. Memproses...', 'yellow');

        $successCount = 0;
        $errorCount = 0;

        foreach ($admins as $admin) {
            try {
                // Generate UUID dari database SSO
                $stmtUuid = $pdoSso->query("SELECT UUID() as uuid");
                $uuid = $stmtUuid->fetch(PDO::FETCH_ASSOC)['uuid'];
                
                $email = $admin['email'] ?? ($admin['username'] . '@mymember.internal');

                // Insert ke SSO Engine
                $stmt = $pdoSso->prepare("INSERT INTO users (id, email, username, password_hash, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $uuid,
                    $email,
                    $admin['username'],
                    $admin['password'], 
                ]);

                // Update sso_user_id di MyMember
                $dbMyMember->table('admin')->where('id_admin', $admin['id_admin'])->update([
                    'sso_user_id' => $uuid,
                    'email'       => $email // Update email jika sebelumnya NULL
                ]);

                CLI::write(" Berhasil sinkronisasi admin: {$admin['username']}", 'green');
                $successCount++;
            } catch (\Exception $e) {
                CLI::error("Gagal sinkronisasi admin {$admin['username']}: " . $e->getMessage());
                $errorCount++;
            }
        }

        CLI::newLine();
        CLI::write("Selesai! Berhasil: {$successCount}, Gagal: {$errorCount}", 'cyan');
    }
}
