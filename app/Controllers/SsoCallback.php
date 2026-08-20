<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Libraries\SsoPublicKey;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

/**
 * SsoCallback
 *
 * Menangani redirect dari SSO Engine setelah login sukses.
 * Memvalidasi JWT, menjalankan JIT provisioning, dan membuat sesi CI4.
 */
class SsoCallback extends BaseController
{
    public function handle()
    {
        // Ambil parameter dari query string
        $accessToken  = $this->request->getGet('access_token');
        $refreshToken = $this->request->getGet('refresh_token');
        $state        = $this->request->getGet('state');

        // Validasi ketersediaan parameter
        if (empty($accessToken) || empty($refreshToken) || empty($state)) {
            return redirect()->to('/login')
                ->with('msg', 'Parameter callback tidak lengkap. Silakan login ulang.');
        }

        // Validasi state (anti-CSRF)
        $savedState = session()->get('sso_state');
        if (empty($savedState) || $state !== $savedState) {
            session()->remove('sso_state');
            return redirect()->to('/login')
                ->with('msg', 'State tidak valid (kemungkinan CSRF). Silakan login ulang.');
        }
        session()->remove('sso_state');

        // Decode & validasi JWT
        $claims = $this->validateJwt($accessToken);

        if ($claims === null) {
            return redirect()->to('/login')
                ->with('msg', 'Token SSO tidak valid atau sudah kadaluarsa. Silakan login ulang.');
        }

        // JIT Provisioning cek/insert/update tabel admin lokal
        $admin = $this->jitProvision($claims);

        session()->set([
            'id_admin'         => $admin['id_admin'],
            'username'         => $admin['username'],
            'nama_resepsionis' => $admin['nama_resepsionis'],
            'email'            => $admin['email'],
            'sso_user_id'      => $claims->sub,
            'jti'              => $claims->jti ?? null,
            'access_token'     => $accessToken,
            'refresh_token'    => $refreshToken,
            'token_expires_at' => $claims->exp,
            'logged_in'        => true,
        ]);

        // Redirect ke dashboard
        return redirect()->to('/admin/dashboard');
    }

    /**
     * Validasi JWT menggunakan public key SSO.
     * Dengan retry: jika signature gagal, force-refresh cache key lalu coba ulang 1x.
     *
     * @param string $token
     * @return object|null Decoded claims, atau null jika gagal
     */
    private function validateJwt(string $token): ?object
    {
        $ssoPublicKey = new SsoPublicKey();

        // pakai cached key
        try {
            $publicKey = $ssoPublicKey->getKey(false);
            return JWT::decode($token, new Key($publicKey, 'RS256'));
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            log_message('warning', '[SsoCallback] Signature mismatch, force-refreshing public key...');
        } catch (Exception $e) {
            log_message('error', '[SsoCallback] JWT validation failed: ' . $e->getMessage());
            return null;
        }

        // force-refresh key (mungkin key sudah dirotasi)
        try {
            $publicKey = $ssoPublicKey->getKey(true);
            return JWT::decode($token, new Key($publicKey, 'RS256'));
        } catch (Exception $e) {
            log_message('error', '[SsoCallback] JWT validation failed after key refresh: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * JIT Provisioning: cek/insert/update admin di tabel lokal.
     *
     * Lookup order:
     * 1. By sso_user_id (paling akurat)
     * 2. By email (fallback untuk admin existing)
     * 3. Insert baru jika tidak ditemukan
     *
     * @param object $claims JWT claims (sub, email, username)
     * @return array Row admin dari database
     */
    private function jitProvision(object $claims): array
    {
        $model = new AdminModel();
        $now   = date('Y-m-d H:i:s');

        // Lookup 1: by sso_user_id
        $admin = $model->where('sso_user_id', $claims->sub)->first();

        if (!$admin) {
            // Lookup 2: by email
            $admin = $model->where('email', $claims->email)->first();
        }

        if ($admin) {
            // UPDATE sinkronisasi data dari SSO
            $updateData = [
                'sso_user_id' => $claims->sub,
                'email'       => $claims->email,
                'synced_at'   => $now,
            ];

            // Update username hanya jika admin tidak punya username kustom
            // (artinya username saat ini = email placeholder)
            if (empty($admin['username']) || $admin['username'] === $claims->username) {
                $updateData['username'] = $claims->username;
            }

            $model->update($admin['id_admin'], $updateData);

            // Reload data terbaru
            return $model->find($admin['id_admin']);
        }

        // INSERT admin baru via JIT
        $model->insert([
            'username'         => $claims->username,
            'password'         => '',  // Tidak dipakai login via SSO
            'nama_resepsionis' => $claims->username, // Placeholder, bisa diedit nanti
            'email'            => $claims->email,
            'sso_user_id'      => $claims->sub,
            'synced_at'        => $now,
        ]);

        $newId = $model->getInsertID();
        return $model->find($newId);
    }
}
