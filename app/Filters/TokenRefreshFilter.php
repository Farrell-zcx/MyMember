<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Libraries\SsoPublicKey;
use Exception;

/**
 * TokenRefreshFilter
 *
 * Filter yang berjalan setelah SsoAuthFilter (hanya jika sudah logged_in).
 * Mengecek apakah access token hampir expired, dan jika ya,
 * melakukan silent refresh menggunakan refresh token.
 */
class TokenRefreshFilter implements FilterInterface
{
    /**
     * Buffer waktu (detik) sebelum token benar-benar expired.
     * Refresh dilakukan 60 detik sebelum expiry untuk menghindari race condition.
     */
    private const REFRESH_BUFFER = 60;

    public function before(RequestInterface $request, $arguments = null)
    {
        // Hanya proses jika user sudah login
        if (!session()->get('logged_in')) {
            return;
        }

        $expiresAt = session()->get('token_expires_at');

        // Jika tidak ada info expiry, skip (safety)
        if (empty($expiresAt)) {
            return;
        }

        // Token masih valid (dengan buffer)? Lanjut
        if (time() < ($expiresAt - self::REFRESH_BUFFER)) {
            return;
        }

        // Token hampir/sudah expired → silent refresh
        $refreshToken = session()->get('refresh_token');
        if (empty($refreshToken)) {
            // Tidak ada refresh token → paksa re-login
            return $this->forceRelogin();
        }

        try {
            $result = $this->doRefresh($refreshToken);

            // Decode JWT baru untuk mendapatkan exp
            $ssoPublicKey = new SsoPublicKey();
            $publicKey = $ssoPublicKey->getKey();
            $decoded = JWT::decode($result['access_token'], new Key($publicKey, 'RS256'));

            // Update session dengan token baru
            session()->set([
                'access_token'     => $result['access_token'],
                'refresh_token'    => $result['refresh_token'],
                'token_expires_at' => $decoded->exp,
            ]);

            log_message('info', '[TokenRefresh] Silent refresh berhasil untuk admin ID: ' . session()->get('id_admin'));
        } catch (Exception $e) {
            log_message('error', '[TokenRefresh] Silent refresh gagal: ' . $e->getMessage());
            // Refresh gagal → paksa re-login
            return $this->forceRelogin();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi setelah response
    }

    /**
     * Panggil SSO Engine POST /refresh-token
     *
     * @param string $refreshToken
     * @return array ['access_token' => '...', 'refresh_token' => '...']
     * @throws Exception
     */
    private function doRefresh(string $refreshToken): array
    {
        $ssoBaseUrl = env('sso.baseUrl');
        $clientId   = env('sso.clientId');

        $url = rtrim($ssoBaseUrl, '/') . '/refresh-token';

        $client = \Config\Services::curlrequest();
        $response = $client->post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode([
                'refresh_token' => $refreshToken,
                'client_id'     => $clientId,
            ]),
            'timeout'     => 10,
            'http_errors' => false,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new Exception("SSO /refresh-token returned HTTP {$response->getStatusCode()}");
        }

        $data = json_decode($response->getBody(), true);

        if (empty($data['access_token']) || empty($data['refresh_token'])) {
            throw new Exception('SSO /refresh-token response tidak mengandung token yang valid.');
        }

        return $data;
    }

    /**
     * Destroy session dan redirect ke SSO untuk re-login.
     */
    private function forceRelogin()
    {
        session()->destroy();

        $state = bin2hex(random_bytes(16));
        session()->set('sso_state', $state);

        $ssoBaseUrl  = env('sso.baseUrl');
        $clientId    = env('sso.clientId');
        $redirectUri = env('sso.redirectUri');

        $authorizeUrl = rtrim($ssoBaseUrl, '/') . '/authorize?' . http_build_query([
            'client_id'    => $clientId,
            'redirect_uri' => $redirectUri,
            'state'        => $state,
        ]);

        return redirect()->to($authorizeUrl);
    }
}
