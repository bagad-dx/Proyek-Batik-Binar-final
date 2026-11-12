<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan pengiriman OTP (disimpan di tabel `pengaturan_otp`).
 *
 * Nilai di sini MENIMPA config mail / Fonnte saat aplikasi boot, jadi
 * admin bisa mengisi kredensial dari panel tanpa mengedit .env.
 * Bila kredensial di sini kosong, aplikasi memakai nilai .env seperti biasa.
 */
class OtpSetting extends Model
{
    protected $table = 'pengaturan_otp';

    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    /** Nilai bawaan bila baris belum ada di database. */
    public const DEFAULTS = [
        'email_enabled' => '1',
        'wa_enabled' => '1',
        'mail_mailer' => 'smtp',
        'mail_host' => 'smtp.gmail.com',
        'mail_port' => '587',
        'mail_username' => '',
        'mail_password' => '',
        'mail_encryption' => 'tls',
        'mail_from_address' => '',
        'mail_from_name' => 'Batik Binar',
        'fonnte_token' => '',
        'demo' => '0',
        'local_fallback' => '0',
    ];

    /** Key yang tidak boleh ditampilkan apa adanya di UI. */
    public const SECRET_KEYS = ['mail_password', 'fonnte_token'];

    /** Cache nilai per request agar tidak query berulang. */
    private static ?array $cache = null;

    private static ?array $rawCache = null;

    private static bool $tableMissing = false;

    public static function get(string $key): string
    {
        return (string) (self::values()[$key] ?? self::DEFAULTS[$key] ?? '');
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        self::$cache = null;
        self::$rawCache = null;
    }

    /** Semua nilai (default + isi database). */
    public static function values(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $db = self::rawValues();

        return self::$cache = array_merge(self::DEFAULTS, $db);
    }

    /** Hanya key yang benar-benar tersimpan di database. */
    public static function rawValues(): array
    {
        if (self::$rawCache !== null) {
            return self::$rawCache;
        }

        if (self::$tableMissing) {
            return [];
        }

        try {
            return self::$rawCache = static::query()->pluck('value', 'key')->all();
        } catch (\Throwable) {
            // Tabel belum dimigrasi / database mati: pakai .env saja.
            self::$tableMissing = true;
            return [];
        }
    }

    public static function emailEnabled(): bool
    {
        return self::get('email_enabled') === '1';
    }

    public static function waEnabled(): bool
    {
        return self::get('wa_enabled') === '1';
    }

    /**
     * Email siap kirim? Dianggap siap bila kredensial SMTP di panel
     * lengkap, ATAU .env sudah memakai transport non-log yang lengkap.
     */
    public static function emailReady(): bool
    {
        self::applyToConfig();

        $mailer = (string) config('mail.default');

        if (in_array($mailer, ['ses', 'ses-v2', 'postmark', 'resend', 'sendmail'], true)) {
            return true;
        }

        if ($mailer !== 'smtp') {
            return false;
        }

        return filled(config('mail.mailers.smtp.host'))
            && filled(config('mail.mailers.smtp.username'))
            && filled(config('mail.mailers.smtp.password'));
    }

    /** WhatsApp siap kirim? Butuh token Fonnte. */
    public static function waReady(): bool
    {
        self::applyToConfig();

        return filled(config('services.fonnte.token'));
    }

    public static function channelAvailable(string $channel): bool
    {
        $enabled = $channel === 'wa' ? self::waEnabled() : self::emailEnabled();

        return $enabled && (self::ready($channel) || self::localFallback());
    }

    /** Kredensial pengiriman asli sudah lengkap? */
    public static function ready(string $channel): bool
    {
        return $channel === 'wa' ? self::waReady() : self::emailReady();
    }

    /**
     * MODE TES LOKAL (khusus pengembangan).
     * Bila aktif dan kredensial belum ada, OTP hanya ditulis ke
     * storage/logs/laravel.log supaya alur daftar bisa diuji tanpa
     * Gmail App Password / token Fonnte. Kode TIDAK tampil di layar.
     */
    public static function localFallback(): bool
    {
        return (bool) config('services.otp.local_fallback', false);
    }

    /** Terapkan pengaturan database ke config Laravel (dipanggil saat boot). */
    public static function applyToConfig(): void
    {
        $db = self::rawValues();
        if ($db === []) {
            return;
        }

        $username = trim((string) ($db['mail_username'] ?? ''));
        $password = preg_replace('/\s+/', '', (string) ($db['mail_password'] ?? '')) ?? '';

        // Hanya override SMTP bila username + password sudah diisi di panel.
        if ($username !== '' && $password !== '') {
            $port = (int) ($db['mail_port'] ?? self::DEFAULTS['mail_port']);
            $encryption = strtolower((string) ($db['mail_encryption'] ?? self::DEFAULTS['mail_encryption']));
            $scheme = ($port === 465 || $encryption === 'ssl') ? 'smtps' : 'smtp';

            config([
                'mail.default' => (string) ($db['mail_mailer'] ?? 'smtp') ?: 'smtp',
                'mail.mailers.smtp.host' => (string) ($db['mail_host'] ?? self::DEFAULTS['mail_host']) ?: 'smtp.gmail.com',
                'mail.mailers.smtp.port' => $port ?: 587,
                'mail.mailers.smtp.username' => $username,
                'mail.mailers.smtp.password' => $password,
                'mail.mailers.smtp.scheme' => $scheme,
                'mail.mailers.smtp.encryption' => null,
                'mail.mailers.smtp.timeout' => 20,
                'mail.from.address' => (string) ($db['mail_from_address'] ?? '') ?: $username,
                'mail.from.name' => (string) ($db['mail_from_name'] ?? '') ?: 'Batik Binar',
            ]);
        }

        $token = trim((string) ($db['fonnte_token'] ?? ''));
        if ($token !== '') {
            config(['services.fonnte.token' => $token]);
        }

        if (array_key_exists('demo', $db)) {
            config(['services.otp.demo' => $db['demo'] === '1']);
        }

        if (array_key_exists('local_fallback', $db)) {
            config(['services.otp.local_fallback' => $db['local_fallback'] === '1']);
        }
    }

    /** Ringkasan status untuk panel admin / endpoint config. */
    public static function status(): array
    {
        return [
            'email_enabled' => self::emailEnabled(),
            'email_ready' => self::emailReady(),
            'wa_enabled' => self::waEnabled(),
            'wa_ready' => self::waReady(),
            'email_configured' => self::channelAvailable('email'),
            'wa_configured' => self::channelAvailable('wa'),
            'demo' => (bool) config('services.otp.demo', false),
            'local_fallback' => self::localFallback(),
            'mail_host' => (string) config('mail.mailers.smtp.host'),
            'mail_port' => (int) config('mail.mailers.smtp.port'),
            'mail_username' => (string) config('mail.mailers.smtp.username'),
            'mail_from_address' => (string) config('mail.from.address'),
        ];
    }

    /** Reset cache statis (dipakai test / setelah perubahan massal). */
    public static function flushCache(): void
    {
        self::$cache = null;
        self::$rawCache = null;
        self::$tableMissing = false;
    }

    /** Nilai untuk form (rahasia diganti penanda agar tidak bocor ke HTML). */
    public static function forForm(): array    {
        $values = self::values();

        // Bila database masih kosong, tampilkan nilai efektif dari .env
        // supaya admin tahu konfigurasi mana yang sedang dipakai.
        $values['mail_username'] = $values['mail_username'] ?: (string) config('mail.mailers.smtp.username');

        foreach (self::SECRET_KEYS as $key) {
            $values[$key . '_set'] = filled($values[$key]);
            $values[$key] = '';
        }

        $values['env_mail_username'] = (string) config('mail.mailers.smtp.username');
        $values['env_mail_password_set'] = filled(config('mail.mailers.smtp.password'));
        $values['env_fonnte_set'] = filled(config('services.fonnte.token'));

        return $values;
    }
}
