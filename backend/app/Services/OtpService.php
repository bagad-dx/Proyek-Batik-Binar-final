<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\OtpSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Pengiriman kode OTP pendaftaran lewat Email (SMTP) atau WhatsApp (Fonnte).
 *
 * Tidak ada lagi "fallback demo" di sini: bila kredensial belum diisi atau
 * pengiriman gagal, hasilnya `sent = false` + pesan error yang jelas.
 * Kode OTP TIDAK pernah ditulis ke log, KECUALI admin mengaktifkan
 * MODE TES LOKAL (tabel pengaturan_otp.local_fallback = 1) yang menulis
 * kode ke storage/logs/otp-log.log untuk keperluan pengembangan.
 */
class OtpService
{
    /** Jeda minimal antar permintaan kode (detik). */
    public const RESEND_SECONDS = 30;

    /** Kode 6 digit tanpa nol di depan yang ambigu (100000-999999). */
    public static function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    /**
     * Normalisasi nomor Indonesia ke format internasional 62...:
     * "0812..." -> "62812...", "+62812" -> "62812", "62812" tetap.
     */
    public static function normalizePhone(string $phone): string
    {
        $d = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($d, '0')) {
            $d = '62' . substr($d, 1);
        } elseif (str_starts_with($d, '8')) {
            $d = '62' . $d;
        }
        // "620812..." (salah ketik) -> "62812..."
        if (str_starts_with($d, '620')) {
            $d = '62' . substr($d, 3);
        }

        return $d;
    }

    public static function isValidPhone(string $phone): bool
    {
        $n = self::normalizePhone($phone);

        return (bool) preg_match('/^62\d{8,13}$/', $n);
    }

    /** Samarkan tujuan agar tidak tampil utuh di layar/log. */
    public static function maskEmail(string $email): string
    {
        [$user, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $keep = min(2, max(1, strlen($user)));
        $masked = substr($user, 0, $keep) . str_repeat('*', max(2, strlen($user) - $keep));

        return $masked . ($domain !== '' ? '@' . $domain : '');
    }

    public static function maskPhone(string $phone): string
    {
        $n = self::normalizePhone($phone);
        if (strlen($n) <= 7) {
            return $n;
        }

        return substr($n, 0, 5) . str_repeat('*', max(2, strlen($n) - 8)) . substr($n, -3);
    }

    public static function emailConfigured(): bool
    {
        return OtpSetting::channelAvailable('email');
    }

    public static function waConfigured(): bool
    {
        return OtpSetting::channelAvailable('wa');
    }

    public static function channelAvailable(string $channel): bool
    {
        return OtpSetting::channelAvailable($channel === 'wa' ? 'wa' : 'email');
    }

    /**
     * Kirim OTP via channel yang diminta.
     *
     * @return array{sent: bool, via: string, error: ?string, code: ?string}
     *         `code` hanya berisi kode teknis (auth/connection/rejected/...).
     */
    public static function send(string $channel, string $name, string $email, string $phone, string $code): array
    {
        return $channel === 'wa'
            ? self::sendWa($phone, $code, $name)
            : self::sendEmail($email, $code, $name);
    }

    /** Kirim pesan uji dari panel admin (tidak menyimpan apa pun ke DB). */
    public static function sendTest(string $channel, string $destination, string $name = 'Admin'): array
    {
        $code = self::generateCode();

        return $channel === 'wa'
            ? self::sendWa($destination, $code, $name, true)
            : self::sendEmail($destination, $code, $name, true);
    }

    public static function sendEmail(string $email, string $code, string $name, bool $isTest = false): array
    {
        if (! OtpSetting::emailReady()) {
            if (self::canUseLocalMode('email')) {
                return self::localDelivery('email', $email, $code, $isTest);
            }

            return self::fail('email', 'not_configured', 'Pengiriman email belum dikonfigurasi. Isi SMTP di Panel Admin > OTP.');
        }

        try {
            $subject = $isTest ? 'Tes Pengiriman Email BATIK BINAR' : null;
            Mail::to($email)->send(new OtpMail($code, $name, $subject));

            Log::info('OTP email terkirim.', ['to' => self::maskEmail($email), 'test' => $isTest]);

            return ['sent' => true, 'via' => 'email', 'error' => null, 'code' => null];
        } catch (Throwable $e) {
            Log::error('OTP email gagal dikirim.', [
                'to' => self::maskEmail($email),
                'error' => $e->getMessage(),
            ]);

            return self::fail('email', self::classifyEmailError($e->getMessage()), self::humanEmailError($e->getMessage()));
        }
    }

    /**
     * Kirim OTP ke WhatsApp via Fonnte (https://fonnte.com).
     * Token diambil dari panel admin (tabel pengaturan_otp) atau .env FONNTE_TOKEN.
     */
    public static function sendWa(string $phone, string $code, string $name, bool $isTest = false): array
    {
        $target = self::normalizePhone($phone);
        if (! preg_match('/^62\d{8,13}$/', $target)) {
            return self::fail('wa', 'invalid_target', 'Nomor WhatsApp tidak valid. Gunakan format 08xxxxxxxxxx.');
        }

        $token = trim((string) config('services.fonnte.token'));

        if ($token === '' || ! OtpSetting::waReady()) {
            if (self::canUseLocalMode('wa')) {
                return self::localDelivery('wa', $target, $code, $isTest);
            }

            return self::fail('wa', 'not_configured', 'Pengiriman WhatsApp belum dikonfigurasi. Isi token Fonnte di Panel Admin > OTP.');
        }

        $intro = $isTest ? 'Ini pesan UJI dari BATIK BINAR.' : "Halo {$name}! Kode OTP pendaftaran BATIK BINAR Anda:";
        $message = $isTest
            ? "{$intro}\n\nKode uji: *{$code}*\n\nJika Anda tidak meminta pesan ini, abaikan saja."
            : "{$intro} *{$code}*\n\nBerlaku 10 menit. Jangan berikan kode ini ke siapa pun.";

        try {
            $response = Http::timeout(20)
                ->retry(2, 400, function ($exception, $response) {
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException
                        || ($response && $response->serverError());
                }, throw: false)
                ->withHeaders(['Authorization' => $token])
                ->asForm()
                ->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                    'countryCode' => '62',
                ]);

            $json = $response->json();
            $status = is_array($json) ? ($json['status'] ?? null) : null;
            $ok = $response->successful() && ($status === true || $status === 'true');

            if ($ok) {
                Log::info('OTP WhatsApp terkirim.', ['to' => self::maskPhone($target), 'test' => $isTest]);

                return ['sent' => true, 'via' => 'wa', 'error' => null, 'code' => null];
            }

            $reason = is_array($json)
                ? (string) ($json['reason'] ?? $json['message'] ?? '')
                : mb_substr($response->body(), 0, 200);

            Log::warning('OTP WhatsApp gagal dikirim (Fonnte).', [
                'to' => self::maskPhone($target),
                'http' => $response->status(),
                'reason' => $reason,
            ]);

            return self::fail('wa', self::classifyWaError($reason), self::humanWaError($reason));
        } catch (Throwable $e) {
            Log::error('OTP WhatsApp exception.', [
                'to' => self::maskPhone($target),
                'error' => $e->getMessage(),
            ]);

            return self::fail('wa', 'connection', 'Tidak dapat terhubung ke layanan WhatsApp (Fonnte). Periksa koneksi internet server.');
        }
    }

    /** Mode tes lokal hanya jalan bila admin mengaktifkannya di panel. */
    private static function canUseLocalMode(string $channel): bool
    {
        return OtpSetting::localFallback()
            && ($channel === 'wa' ? OtpSetting::waEnabled() : OtpSetting::emailEnabled());
    }

    /**
     * MODE TES LOKAL: tidak mengirim apa pun, hanya menulis kode ke
     * storage/logs/otp-log.log. Kode tidak pernah dikembalikan ke browser.
     */
    private static function localDelivery(string $channel, string $destination, string $code, bool $isTest): array
    {
        $masked = $channel === 'wa'
            ? self::maskPhone($destination)
            : self::maskEmail($destination);

        Log::channel('otp')->warning('[OTP MODE TES LOKAL] Kode OTP tidak dikirim ke ' . $channel . '. Cari baris ini di storage/logs/otp-log.log.', [
            'to' => $masked,
            'code' => $code,
            'test' => $isTest,
        ]);

        Log::warning('[OTP MODE TES LOKAL] Kode OTP tidak dikirim ke ' . $channel . '. Cari baris ini di storage/logs/otp-log.log.', [
            'to' => $masked,
            'code' => $code,
            'test' => $isTest,
        ]);

        return ['sent' => true, 'via' => $channel, 'error' => null, 'code' => null, 'local' => true];
    }

    private static function fail(string $via, string $code, string $message): array
    {
        return ['sent' => false, 'via' => $via, 'error' => $message, 'code' => $code];
    }

    private static function classifyEmailError(string $raw): string
    {
        $e = strtolower($raw);

        return match (true) {
            str_contains($e, '535'), str_contains($e, 'username and password not accepted'),
            str_contains($e, 'invalid login'), str_contains($e, 'authentication') => 'auth',
            str_contains($e, 'connection could not be established'), str_contains($e, 'connection refused'),
            str_contains($e, 'timed out'), str_contains($e, 'timeout'),
            str_contains($e, 'getaddrinfo'), str_contains($e, 'stream_socket_client') => 'connection',
            str_contains($e, 'crypto'), str_contains($e, 'ssl'), str_contains($e, 'tls') => 'encryption',
            default => 'unknown',
        };
    }

    private static function humanEmailError(string $raw): string
    {
        return match (self::classifyEmailError($raw)) {
            'auth' => 'Email pengirim menolak login. Periksa MAIL_USERNAME dan App Password Gmail (16 huruf, bukan password biasa).',
            'connection' => 'Tidak dapat terhubung ke server email. Periksa host/port dan koneksi internet server.',
            'encryption' => 'Koneksi SMTP gagal dienkripsi. Coba port 587 + TLS, atau port 465 + SSL.',
            default => 'Gagal mengirim email. Coba lagi beberapa saat atau hubungi admin.',
        };
    }

    private static function classifyWaError(string $reason): string
    {
        $r = strtolower($reason);

        return match (true) {
            str_contains($r, 'token') => 'auth',
            str_contains($r, 'device'), str_contains($r, 'connect'), str_contains($r, 'disconnect') => 'device',
            str_contains($r, 'target'), str_contains($r, 'number'), str_contains($r, 'nomor') => 'invalid_target',
            default => 'rejected',
        };
    }

    private static function humanWaError(string $reason): string
    {
        return match (self::classifyWaError($reason)) {
            'auth' => 'Token Fonnte ditolak. Salin ulang token dari dashboard Fonnte.',
            'device' => 'Perangkat WhatsApp Fonnte terputus. Buka dashboard Fonnte lalu scan ulang QR.',
            'invalid_target' => 'Nomor WhatsApp tujuan tidak valid atau tidak terdaftar di WhatsApp.',
            default => 'Layanan WhatsApp menolak permintaan. Periksa kuota/pesan Fonnte atau coba lagi.',
        };
    }
}
