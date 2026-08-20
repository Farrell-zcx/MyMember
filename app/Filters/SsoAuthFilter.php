<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * SsoAuthFilter
 *
 * Filter yang melindungi route admin/*.
 * Jika user belum punya sesi CI4, redirect ke SSO Engine /authorize.
 */
class SsoAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Cek sesi login lokal
        if (!session()->get('logged_in')) {
            return $this->redirectToSso();
        }

        // Cek JTI ke Redis Blacklist
        $jti = session()->get('jti');
        if (!empty($jti) && $this->isBlacklisted($jti)) {
            session()->destroy();
            return redirect()->to('/login')->with('msg', 'Sesi Anda telah diakhiri dari aplikasi lain.');
        }
    }

    private function isBlacklisted(string $jti): bool
    {
        $host = env('redis.host', '127.0.0.1');
        $port = (int) env('redis.port', 6379);

        $fp = @fsockopen($host, $port, $errno, $errstr, 0.2);
        if (!$fp) {
            return false;
        }

        $key = 'sso_blacklist:jti:' . $jti;
        $cmd = "*2\r\n$6\r\nEXISTS\r\n$" . strlen($key) . "\r\n" . $key . "\r\n";
        fwrite($fp, $cmd);
        $res = fgets($fp);
        fclose($fp);

        return trim((string) $res) === ':1';
    }

    private function redirectToSso()
    {
        $state = bin2hex(random_bytes(16));
        session()->set('sso_state', $state);

        $ssoBaseUrl   = env('sso.baseUrl', 'http://sso-engine.test');
        $clientId     = env('sso.clientId', 'mymember-app');
        $redirectUri  = env('sso.redirectUri', 'http://mymember.test/auth/callback');

        $authorizeUrl = rtrim($ssoBaseUrl, '/') . '/authorize?' . http_build_query([
            'client_id'    => $clientId,
            'redirect_uri' => $redirectUri,
            'state'        => $state,
        ]);

        return redirect()->to($authorizeUrl);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi setelah response
    }
}
