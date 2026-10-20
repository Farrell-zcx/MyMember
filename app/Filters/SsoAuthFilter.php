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
        $host = getenv('REDIS_HOST') ?: env('redis.host', '127.0.0.1');
        $port = (int) (getenv('REDIS_PORT') ?: env('redis.port', 6379));

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

        $isLocalhost  = isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '.test') !== false);
        $defaultSsoBase  = $isLocalhost ? (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? 'http://sso.localhost' : 'http://sso-engine.test') : 'https://sso.31.97.48.33.sslip.io';
        $defaultRedirect = $isLocalhost ? (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? 'http://mymember.localhost/auth/callback' : 'http://mymember.test/auth/callback') : 'https://mymember.tech/auth/callback';

        $ssoBaseUrl  = getenv('SSO_BASE_URL') ?: (!$isLocalhost ? $defaultSsoBase : env('sso.baseUrl')) ?: $defaultSsoBase;
        $clientId    = getenv('SSO_CLIENT_ID') ?: env('sso.clientId', 'mymember-app');
        $redirectUri = getenv('SSO_REDIRECT_URI') ?: (!$isLocalhost ? $defaultRedirect : env('sso.redirectUri')) ?: $defaultRedirect;

        if (!$isLocalhost) {
            if (strpos($ssoBaseUrl, 'localhost') !== false || strpos($ssoBaseUrl, '.test') !== false) {
                $ssoBaseUrl = $defaultSsoBase;
            }
            if (strpos($redirectUri, 'localhost') !== false || strpos($redirectUri, '.test') !== false) {
                $redirectUri = $defaultRedirect;
            }
            $ssoBaseUrl  = str_replace('http://', 'https://', $ssoBaseUrl);
            $redirectUri = str_replace('http://mymember.tech', 'https://mymember.tech', $redirectUri);
        }

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
