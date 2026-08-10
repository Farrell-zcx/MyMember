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
        // Sudah login? Lanjut ke controller
        if (session()->get('logged_in')) {
            return;
        }

        // Belum login → redirect ke SSO Engine
        $state = bin2hex(random_bytes(16));
        session()->set('sso_state', $state);

        $ssoBaseUrl   = env('sso.baseUrl');
        $clientId     = env('sso.clientId');
        $redirectUri  = env('sso.redirectUri');

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
