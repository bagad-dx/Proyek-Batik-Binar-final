<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Verifikasi Firebase ID Token (Google Sign-In) tanpa Admin SDK.
 * Mengikuti https://firebase.google.com/docs/auth/admin/verify-id-tokens
 */
class FirebaseTokenVerifier
{
    protected const CERTS_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

    /**
     * @return array claims (sub, email, name, picture, ...)
     *
     * @throws \RuntimeException jika token tidak valid
     */
    public function verify(string $idToken, string $projectId): array
    {
        $parts = explode('.', $idToken);
        if (count($parts) !== 3) throw new \RuntimeException('Format token salah.');

        $header = $this->b64decode($parts[0]);
        $payload = $this->b64decode($parts[1]);
        $signature = $this->b64decodeRaw($parts[2]);
        if (! $header || ! $payload || ! $signature) throw new \RuntimeException('Token tidak bisa dibaca.');

        if (($header['alg'] ?? null) !== 'RS256') throw new \RuntimeException('Algoritma token tidak didukung.');
        $kid = $header['kid'] ?? null;
        if (! $kid) throw new \RuntimeException('Token tanpa kid.');

        $certs = $this->certs();
        if (! isset($certs[$kid])) {
            $certs = $this->certs(true); // refresh sekali bila kid baru (rotasi kunci)
            if (! isset($certs[$kid])) throw new \RuntimeException('Kunci penanda tidak dikenal.');
        }

        $ok = openssl_verify($parts[0] . '.' . $parts[1], $signature, $certs[$kid], OPENSSL_ALGO_SHA256);
        if ($ok !== 1) throw new \RuntimeException('Tanda tangan token tidak valid.');

        $now = time();
        if (($payload['aud'] ?? null) !== $projectId) throw new \RuntimeException('Audience token salah.');
        if (($payload['iss'] ?? null) !== "https://securetoken.google.com/{$projectId}") throw new \RuntimeException('Issuer token salah.');
        if (empty($payload['sub']) || ! is_string($payload['sub'])) throw new \RuntimeException('Subjek token kosong.');
        if (($payload['exp'] ?? 0) <= $now) throw new \RuntimeException('Token kedaluwarsa.');
        if (($payload['iat'] ?? 0) > $now + 300) throw new \RuntimeException('Token dari masa depan.');
        if (($payload['auth_time'] ?? 0) > $now + 300) throw new \RuntimeException('Waktu auth tidak valid.');

        return $payload;
    }

    protected function certs(bool $refresh = false): array
    {
        if ($refresh) Cache::forget('firebase_certs');
        return Cache::remember('firebase_certs', 3600, function () {
            $res = Http::timeout(10)->get(self::CERTS_URL);
            if (! $res->successful()) throw new \RuntimeException('Gagal mengambil sertifikat Google.');
            return $res->json();
        });
    }

    protected function b64decode(string $s): ?array
    {
        $d = json_decode($this->b64decodeRaw($s), true);
        return is_array($d) ? $d : null;
    }

    protected function b64decodeRaw(string $s): string|false
    {
        return base64_decode(strtr($s, '-_', '+/'));
    }
}
