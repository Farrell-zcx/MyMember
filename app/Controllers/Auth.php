<?php

namespace App\Controllers;

use App\Models\AdminModel;

class Auth extends BaseController
{
    /**
     * Redirect ke SSO Engine /authorize.
     * Route /login dan /register sekarang mengarah ke sini.
     */
    public function login()
    {
        // Jika sudah login, langsung ke dashboard
        if (session()->get('logged_in')) {
            return redirect()->to('/admin/dashboard');
        }

        // Redirect ke SSO Engine
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

    /**
     * Register dinonaktifkan — redirect ke SSO.
     */
    public function register()
    {
        return $this->login();
    }

    /**
     * Logout dengan Single Logout (SLO).
     *
     * 1. Panggil SSO Engine POST /logout (revoke refresh token + blacklist JTI)
     * 2. Destroy session lokal
     * 3. Redirect ke SSO /authorize
     */
    public function logout()
    {
        $refreshToken = session()->get('refresh_token');

        // Panggil SSO logout (best-effort, abaikan error)
        if (!empty($refreshToken)) {
            try {
                $ssoBaseUrl = env('sso.baseUrl');
                $url = rtrim($ssoBaseUrl, '/') . '/logout';

                $client = \Config\Services::curlrequest();
                $client->post($url, [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body'    => json_encode([
                        'refresh_token' => $refreshToken,
                    ]),
                    'timeout'     => 10,
                    'http_errors' => false,
                ]);
            } catch (\Exception $e) {
                log_message('error', '[Auth::logout] SSO logout call failed: ' . $e->getMessage());
                // Tetap lanjut destroy session lokal
            }
        }

        // Destroy session lokal
        session()->destroy();

        // Redirect ke SSO untuk login ulang
        return $this->login();
    }
}