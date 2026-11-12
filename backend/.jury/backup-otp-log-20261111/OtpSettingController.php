<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OtpSetting;
use App\Services\OtpService;
use Illuminate\Http\Request;

/**
 * Pengaturan pengiriman OTP (SMTP Email + WhatsApp Fonnte) dari panel admin.
 * Nilai disimpan di tabel `pengaturan_otp` dan langsung dipakai request
 * berikutnya tanpa perlu mengedit .env atau restart server.
 */
class OtpSettingController extends Controller
{
    public function index()
    {
        return view('admin.otp.index', [
            's' => OtpSetting::forForm(),
            'status' => OtpSetting::status(),
            'resendSeconds' => OtpService::RESEND_SECONDS,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|between:1,65535',
            'mail_username' => 'nullable|email|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl,none',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:100',
            'fonnte_token' => 'nullable|string|max:255',
        ]);

        OtpSetting::set('email_enabled', $request->boolean('email_enabled') ? '1' : '0');
        OtpSetting::set('wa_enabled', $request->boolean('wa_enabled') ? '1' : '0');

        // Konfigurasi SMTP (email)
        OtpSetting::set('mail_host', trim((string) $request->input('mail_host')));
        OtpSetting::set('mail_port', (string) ($request->integer('mail_port') ?: 587));
        OtpSetting::set('mail_username', trim((string) $request->input('mail_username')));
        OtpSetting::set('mail_encryption', (string) ($request->input('mail_encryption') ?: 'tls'));
        OtpSetting::set('mail_from_address', trim((string) $request->input('mail_from_address')));
        OtpSetting::set('mail_from_name', trim((string) $request->input('mail_from_name')) ?: 'Batik Binar');

        // Password / token hanya diganti bila field diisi, supaya nilai
        // lama tidak terhapus saat admin menyimpan perubahan lain.
        if ($request->boolean('clear_mail_password')) {
            OtpSetting::set('mail_password', '');
        } elseif ($request->filled('mail_password')) {
            // App Password Gmail sering disalin dengan spasi: buang spasinya.
            OtpSetting::set('mail_password', preg_replace('/\s+/', '', (string) $request->input('mail_password')));
        }

        if ($request->boolean('clear_fonnte_token')) {
            OtpSetting::set('fonnte_token', '');
        } elseif ($request->filled('fonnte_token')) {
            OtpSetting::set('fonnte_token', trim((string) $request->input('fonnte_token')));
        }

        OtpSetting::applyToConfig();

        $status = OtpSetting::status();
        $msg = 'Pengaturan OTP disimpan.';
        if ($status['local_fallback']) {
            $msg .= ' MODE TES LOKAL masih AKTIF: OTP hanya masuk log, bukan email/WA.';
        }
        if ($status['email_enabled'] && ! $status['email_ready']) {
            $msg .= ' Email belum siap: isi username + App Password Gmail.';
        }
        if ($status['wa_enabled'] && ! $status['wa_ready']) {
            $msg .= ' WhatsApp belum siap: isi token Fonnte.';
        }

        return back()->with('success', $msg);
    }

    /**
     * Mode tes lokal: OTP hanya ditulis ke storage/logs/laravel.log.
     * Dipakai saat kredensial Gmail/Fonnte belum tersedia.
     */
    public function toggleLocalMode(Request $request)
    {
        $on = $request->boolean('local_fallback');

        OtpSetting::set('local_fallback', $on ? '1' : '0');
        OtpSetting::applyToConfig();

        return back()->with('success', $on
            ? 'MODE TES LOKAL AKTIF. Kode OTP hanya ditulis ke storage/logs/laravel.log dan TIDAK dikirim ke email/WhatsApp. Matikan lagi setelah kredensial asli siap.'
            : 'Mode tes lokal DIMATIKAN. OTP kembali wajib dikirim lewat Email/Fonnte.');
    }

    /** Kirim pesan uji nyata (email / WhatsApp) untuk memastikan kredensial benar. */
    public function test(Request $request)
    {
        $data = $request->validate([
            'channel' => 'required|in:email,wa',
            'destination' => 'required|string|max:255',
        ]);

        if ($data['channel'] === 'email') {
            if (! filter_var($data['destination'], FILTER_VALIDATE_EMAIL)) {
                return back()->with('error', 'Alamat email uji tidak valid.');
            }
            if (! OtpService::channelAvailable('email')) {
                return back()->with('error', 'Email belum siap. Simpan username + App Password Gmail dulu.');
            }
        } else {
            if (! OtpService::isValidPhone($data['destination'])) {
                return back()->with('error', 'Nomor WhatsApp uji tidak valid. Gunakan format 08xxxxxxxxxx.');
            }
            if (! OtpService::channelAvailable('wa')) {
                return back()->with('error', 'WhatsApp belum siap. Simpan token Fonnte dulu.');
            }
        }

        $result = OtpService::sendTest($data['channel'], $data['destination'], 'Admin Batik Binar');

        if (! $result['sent']) {
            return back()->with('error', 'Gagal kirim uji: ' . ($result['error'] ?? 'kesalahan tidak diketahui'));
        }

        return back()->with('success', ! empty($result['local'])
            ? 'MODE TES LOKAL: kode uji ditulis ke storage/logs/laravel.log (tidak dikirim ke email/WA).'
            : 'Pesan uji berhasil dikirim ke ' . $data['destination'] . '. Cek inbox/WA sekarang.');
    }
}
