<?php

namespace App\Libraries;

use Exception;

/**
 * SsoPublicKey
 *
 * Fetch & cache SSO Engine public key ke file lokal.
 * Auto-expire setelah 24 jam, dengan graceful degradation
 * jika SSO Engine sedang tidak available.
 */
class SsoPublicKey
{
    /**
     * Path file cache public key
     */
    private string $cachePath;

    /**
     * TTL cache dalam detik (24 jam)
     */
    private const CACHE_TTL = 86400;

    public function __construct()
    {
        $this->cachePath = WRITEPATH . 'sso/public.pem';
    }

    /**
     * Ambil public key — dari cache jika masih valid, atau fetch baru dari SSO.
     *
     * @param bool $forceRefresh Paksa fetch ulang (misal setelah signature mismatch)
     * @return string PEM-encoded public key
     * @throws Exception Jika gagal mendapatkan public key dari manapun
     */
    public function getKey(bool $forceRefresh = false): string
    {
        // 1. Cek cache (jika tidak force-refresh)
        if (!$forceRefresh && $this->isCacheValid()) {
            $key = file_get_contents($this->cachePath);
            if ($key !== false && !empty(trim($key))) {
                return $key;
            }
        }

        // 2. Fetch dari SSO Engine
        try {
            $key = $this->fetchFromSso();
            $this->saveToCache($key);
            return $key;
        } catch (Exception $e) {
            log_message('error', '[SsoPublicKey] Fetch from SSO failed: ' . $e->getMessage());

            // 3. Graceful degradation: gunakan cache lama jika ada
            if (file_exists($this->cachePath)) {
                $key = file_get_contents($this->cachePath);
                if ($key !== false && !empty(trim($key))) {
                    log_message('warning', '[SsoPublicKey] Using stale cached key as fallback.');
                    return $key;
                }
            }

            // 4. Tidak ada cache sama sekali
            throw new Exception('Tidak dapat memperoleh public key SSO. SSO Engine mungkin sedang down.');
        }
    }

    /**
     * Cek apakah file cache masih valid (ada dan belum expired).
     */
    private function isCacheValid(): bool
    {
        if (!file_exists($this->cachePath)) {
            return false;
        }

        $fileAge = time() - filemtime($this->cachePath);
        return $fileAge < self::CACHE_TTL;
    }

    /**
     * Fetch public key dari SSO Engine via HTTP GET.
     *
     * @return string PEM public key
     * @throws Exception
     */
    private function fetchFromSso(): string
    {
        $ssoBaseUrl = env('sso.baseUrl');
        if (empty($ssoBaseUrl)) {
            throw new Exception('sso.baseUrl belum dikonfigurasi di .env');
        }

        $url = rtrim($ssoBaseUrl, '/') . '/public-key';

        $client = \Config\Services::curlrequest();
        $response = $client->get($url, [
            'timeout' => 10,
            'http_errors' => false,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new Exception("SSO /public-key returned HTTP {$response->getStatusCode()}");
        }

        $key = $response->getBody();
        if (empty(trim($key))) {
            throw new Exception('SSO /public-key returned empty response');
        }

        return $key;
    }

    /**
     * Simpan public key ke file cache.
     */
    private function saveToCache(string $key): void
    {
        $dir = dirname($this->cachePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->cachePath, $key);
    }

    /**
     * Hapus cache manual (untuk invalidation).
     */
    public function clearCache(): void
    {
        if (file_exists($this->cachePath)) {
            unlink($this->cachePath);
        }
    }
}
