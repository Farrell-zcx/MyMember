<?php

namespace App\Controllers;

class Auth extends BaseController
{
    /**
     * Redirect ke SSO Engine /authorize.
     * Route /login dan /register sekarang mengarah ke sini.
     */
    public function login()
    {
        // Jika ada pesan error dari callback, jangan redirect untuk mencegah infinite loop
        if (session()->getFlashdata('msg')) {
            return view('auth/login', ['msg' => session()->getFlashdata('msg')]);
        }

        // Jika sudah login, langsung ke dashboard
        if (session()->get('logged_in')) {
            return redirect()->to('/admin/dashboard');
        }

        // Redirect ke SSO Engine
        $state = bin2hex(random_bytes(16));
        session()->set('sso_state', $state);

        $isLocalhost  = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;
        $isProduction = isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'mymember.tech') !== false || strpos($_SERVER['HTTP_HOST'], 'sslip.io') !== false);

        $defaultSsoBase  = $isProduction ? 'https://sso.31.97.48.33.sslip.io' : ($isLocalhost ? 'http://sso.localhost' : 'http://sso-engine.test');
        $defaultRedirect = $isProduction ? 'https://mymember.tech/auth/callback' : ($isLocalhost ? 'http://mymember.localhost/auth/callback' : 'http://mymember.test/auth/callback');

        $ssoBaseUrl  = getenv('SSO_BASE_URL') ?: ($isProduction ? $defaultSsoBase : env('sso.baseUrl')) ?: $defaultSsoBase;
        $clientId    = getenv('SSO_CLIENT_ID') ?: env('sso.clientId', 'mymember-app');
        $redirectUri = getenv('SSO_REDIRECT_URI') ?: ($isProduction ? $defaultRedirect : env('sso.redirectUri')) ?: $defaultRedirect;

        if ($isProduction || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            $ssoBaseUrl  = str_replace('http://sso.', 'https://sso.', $ssoBaseUrl);
            $redirectUri = str_replace('http://mymember.tech', 'https://mymember.tech', $redirectUri);
        }

        $authorizeUrl = rtrim($ssoBaseUrl, '/') . '/authorize?' . http_build_query([
            'client_id'    => $clientId,
            'redirect_uri' => $redirectUri,
            'state'        => $state,
        ]);

        return redirect()->to($authorizeUrl);
    }

    /**
     * Register dinonaktifkan pendaftaran hanya melalui Admin SSO Pusat.
     */
    public function register()
    {
        return redirect()->to('/login');
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
                $ssoInternalUrl = getenv('SSO_INTERNAL_URL') ?: env('sso.internalUrl') ?: getenv('SSO_BASE_URL') ?: env('sso.baseUrl', 'http://sso-engine.test');
                $url = rtrim($ssoInternalUrl, '/') . '/logout';

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

        // Redirect ke SSO Engine logout-web dengan redirect_to beranda MyMember
        $ssoBaseUrl = getenv('SSO_BASE_URL') ?: env('sso.baseUrl', 'http://sso.localhost');
        $logoutUrl = rtrim($ssoBaseUrl, '/') . '/logout-web?redirect_to=' . urlencode(base_url('/'));
        
        return redirect()->to($logoutUrl);
    }
}