<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OtpSetting;
use App\Models\OtpVerification;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class OtpController extends Controller
{
    /**
     * Langkah 1 pendaftaran: validasi data + kirim kode OTP 6 digit
     * ke Email (SMTP) atau WhatsApp (Fonnte) sesuai `channel`.
     *
     * Bila pengiriman gagal, pendaftaran DITOLAK dengan pesan jelas.
     * MODE DEMO (pengaturan_otp.demo = 1): kode OTP asli juga
     * dikembalikan di respons sebagai `demo_code` agar tampil di
     * halaman register; verifikasi tetap lewat /otp/verify.
     */
    public function request(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'username' => 'nullable|string|min:3|max:30|alpha_dash|unique:pengguna,username',
            'email' => 'required|email|max:255|unique:pengguna,email',
            'phone' => ['required', 'string', 'min:9', 'max:20', $this->phoneRule()],
            'password' => ['required', 'confirmed', Password::min(6)],
            'channel' => 'nullable|in:email,wa',
        ]);

        $channel = $data['channel'] ?? 'email';
        $email = strtolower(trim($data['email']));
        $phone = OtpService::normalizePhone($data['phone']);

        if (! OtpService::channelAvailable($channel)) {
            return $this->unavailable($channel);
        }

        // Cooldown di sisi server (tombol frontend saja tidak cukup).
        $existing = OtpVerification::where('email', $email)->first();
        if ($existing && $wait = $this->cooldownRemaining($existing)) {
            return response()->json([
                'message' => "Tunggu {$wait} detik sebelum meminta kode lagi.",
                'retry_after' => $wait,
            ], 429);
        }

        if (empty($data['username'])) {
            $data['username'] = $this->uniqueUsername($email);
        }

        $code = OtpService::generateCode();

        $pending = OtpVerification::updateOrCreate(
            ['email' => $email],
            [
                'name' => $data['name'],
                'username' => $data['username'],
                'phone' => $phone,
                'password' => Hash::make($data['password']),
                'channel' => $channel,
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(OtpVerification::TTL_MINUTES),
                'last_sent_at' => null,
            ]
        );

        $result = OtpService::send($channel, $data['name'], $email, $phone, $code);

        if (! $result['sent']) {
            // Jangan tinggalkan calon akun tanpa kode yang bisa dipakai.
            $pending->delete();

            return $this->deliveryFailed($result, $channel);
        }

        $pending->update(['last_sent_at' => now()]);

        Log::info('OTP pendaftaran terkirim.', [
            'email' => OtpService::maskEmail($email),
            'channel' => $channel,
        ]);

        return response()->json($this->sentPayload($result, $channel, $email, $phone, 201, $code), 201);
    }

    /** Kirim ulang kode baru (rate-limit via throttle route + cooldown server). */
    public function resend(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'channel' => 'nullable|in:email,wa',
        ]);

        $email = strtolower(trim($data['email']));
        $pending = OtpVerification::where('email', $email)->first();

        if (! $pending) {
            return response()->json([
                'message' => 'Tidak ada pendaftaran menunggu untuk email ini. Isi form daftar dulu.',
            ], 404);
        }

        if ($wait = $this->cooldownRemaining($pending)) {
            return response()->json([
                'message' => "Tunggu {$wait} detik sebelum mengirim ulang.",
                'retry_after' => $wait,
            ], 429);
        }

        $channel = $data['channel'] ?? $pending->channel;

        if (! OtpService::channelAvailable($channel)) {
            return $this->unavailable($channel);
        }

        $code = OtpService::generateCode();
        $result = OtpService::send($channel, $pending->name, $pending->email, $pending->phone, $code);

        if (! $result['sent']) {
            // Kode lama tetap berlaku; jangan ubah baris pending.
            return $this->deliveryFailed($result, $channel);
        }

        $pending->update([
            'channel' => $channel,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(OtpVerification::TTL_MINUTES),
            'last_sent_at' => now(),
        ]);

        return response()->json($this->sentPayload($result, $channel, $pending->email, $pending->phone, 200, $code));
    }

    /**
     * Langkah 2: verifikasi kode -> buat akun di `pengguna` -> token.
     * Maks 5x salah, kedaluwarsa 10 menit, sekali pakai.
     */
    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $email = strtolower(trim($data['email']));

        return DB::transaction(function () use ($email, $data) {
            $pending = OtpVerification::where('email', $email)->lockForUpdate()->first();

            if (! $pending) {
                return response()->json(['message' => 'Tidak ada pendaftaran menunggu untuk email ini.'], 404);
            }
            if ($pending->isExpired()) {
                $pending->delete();

                return response()->json(['message' => 'Kode kedaluwarsa. Minta kode baru.'], 410);
            }
            if ($pending->isLocked()) {
                $pending->delete();

                return response()->json(['message' => 'Terlalu banyak salah (5x). Minta kode baru.'], 429);
            }
            if (User::where('email', $pending->email)->exists()) {
                $pending->delete();

                return response()->json(['message' => 'Email ini sudah terdaftar. Silakan masuk.'], 409);
            }

            if (! Hash::check($data['code'], $pending->code_hash)) {
                $pending->increment('attempts');
                $sisa = OtpVerification::MAX_ATTEMPTS - $pending->fresh()->attempts;

                return response()->json(['message' => "Kode salah. Sisa percobaan: {$sisa}x."], 422);
            }

            $user = User::create([
                'name' => $pending->name,
                'username' => $pending->username,
                'email' => $pending->email,
                'phone' => $pending->phone,
                'password' => $pending->password, // sudah di-hash saat request
                'role' => 'user',
                'email_verified_at' => now(), // email benar-benar sudah dibuktikan
            ]);
            $pending->delete();

            $token = $user->createToken('batik-binar')->plainTextToken;

            return response()->json([
                'message' => 'Verifikasi berhasil! Akun Anda aktif.',
                'user' => $user,
                'token' => $token,
            ], 201);
        });
    }

    /** Info untuk frontend: channel apa yang aktif + durasi OTP. */
    public function config(): JsonResponse
    {
        $status = OtpSetting::status();

        return response()->json([
            'channels' => array_values(array_filter(
                ['email', 'wa'],
                fn (string $c) => $c === 'wa' ? $status['wa_configured'] : $status['email_configured']
            )),
            'wa_configured' => $status['wa_configured'],
            'email_configured' => $status['email_configured'],
            'wa_enabled' => $status['wa_enabled'],
            'email_enabled' => $status['email_enabled'],
            'demo' => $status['demo'],
            'local_fallback' => $status['local_fallback'],
            'delivery' => $status['local_fallback'] ? 'log' : 'live',
            'ttl_minutes' => OtpVerification::TTL_MINUTES,
            'max_attempts' => OtpVerification::MAX_ATTEMPTS,
            'resend_seconds' => OtpService::RESEND_SECONDS,
        ]);
    }

    // ------------------------------------------------------------------

    private function phoneRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (! OtpService::isValidPhone((string) $value)) {
                $fail('Nomor WhatsApp tidak valid. Gunakan format 08xxxxxxxxxx.');
            }
        };
    }

    private function uniqueUsername(string $email): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '', explode('@', $email)[0]) ?: 'user');
        $username = substr($base, 0, 24);

        for ($i = 1; User::where('username', $username)->exists()
            || OtpVerification::where('username', $username)->exists(); $i++) {
            $username = substr($base, 0, 24) . $i;
        }

        return $username;
    }

    private function cooldownRemaining(OtpVerification $pending): int
    {
        if (! $pending->last_sent_at) {
            return 0;
        }

        $elapsed = (int) $pending->last_sent_at->diffInSeconds(now());

        return max(0, OtpService::RESEND_SECONDS - $elapsed);
    }

    private function sentPayload(array $result, string $channel, string $email, string $phone, int $status = 200, ?string $code = null): array
    {
        $dest = $channel === 'wa'
            ? 'WhatsApp ' . OtpService::maskPhone($phone)
            : 'email ' . OtpService::maskEmail($email);

        $local = ! empty($result['local']);

        $payload = [
            'message' => $local
                ? 'MODE TES LOKAL aktif: kode OTP ditulis ke storage/logs/laravel.log, tidak dikirim ke email/WhatsApp.'
                : "Kode OTP dikirim ke {$dest}. Berlaku " . OtpVerification::TTL_MINUTES . ' menit.',
            'email' => $email,
            'channel' => $channel,
            'via' => $result['via'],
            'sent' => true,
            'local' => $local,
            'expires_in' => OtpVerification::TTL_MINUTES * 60,
            'resend_in' => OtpService::RESEND_SECONDS,
        ];

        // MODE DEMO: kembalikan kode OTP ASLI di respons agar bisa tampil
        // di halaman register. Hanya aktif bila mode demo menyala
        // (pengaturan_otp.demo = 1 atau OTP_DEMO=true). Verifikasi tetap
        // membandingkan hash di database, jadi ini bukan bypass.
        if ($code !== null && (config('services.otp.demo') || OtpSetting::get('demo') === '1')) {
            $payload['demo_code'] = $code;
        }

        return $payload;
    }

    private function deliveryFailed(array $result, string $channel): JsonResponse
    {
        return response()->json([
            'message' => $result['error'] ?: 'Kode OTP gagal dikirim. Coba lagi.',
            'channel' => $channel,
            'sent' => false,
            'error_code' => $result['code'] ?? 'unknown',
        ], 502);
    }

    private function unavailable(string $channel): JsonResponse
    {
        $label = $channel === 'wa' ? 'WhatsApp' : 'Email';

        return response()->json([
            'message' => "Verifikasi lewat {$label} belum aktif. Pilih channel lain atau hubungi admin.",
            'channel' => $channel,
            'sent' => false,
            'error_code' => 'not_configured',
        ], 422);
    }
}
