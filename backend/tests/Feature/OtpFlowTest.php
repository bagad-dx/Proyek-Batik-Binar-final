<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\OtpSetting;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Uji alur OTP pendaftaran mode NYATA:
 * - email/WA benar-benar dipanggil (Mail::fake / Http::fake),
 * - kode TIDAK pernah dikembalikan ke browser,
 * - pengiriman gagal = pendaftaran ditolak (502),
 * - cooldown kirim ulang, expiry, max attempt, dan pembuatan akun.
 */
class OtpFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\OtpSetting::flushCache();

        Mail::fake();

        // Anggap SMTP & Fonnte sudah dikonfigurasi (tanpa menyentuh DB).
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.port' => 587,
            'mail.mailers.smtp.username' => 'pengirim@gmail.com',
            'mail.mailers.smtp.password' => 'app-password-uji',
            'services.fonnte.token' => 'token-fonnte-uji',
            'services.otp.demo' => false,
        ]);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Bagas Akfareza',
            'username' => 'bagasuji',
            'email' => 'bagas@example.com',
            'phone' => '081234567890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'channel' => 'email',
        ], $override);
    }

    public function test_email_otp_dikirim_nyata_dan_kode_tidak_bocor_ke_response(): void
    {
        $response = $this->postJson('/api/otp/request', $this->payload());

        $response->assertCreated()
            ->assertJson([
                'sent' => true,
                'channel' => 'email',
                'via' => 'email',
            ])
            ->assertJsonMissingPath('dev_code');

        $code = null;
        Mail::assertSent(OtpMail::class, function (OtpMail $mail) use (&$code) {
            $code = $mail->code;
            return $mail->hasTo('bagas@example.com');
        });

        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $code);
        $this->assertStringNotContainsString((string) $code, $response->getContent());

        $pending = OtpVerification::first();
        $this->assertNotNull($pending);
        $this->assertSame('6281234567890', $pending->phone);
        $this->assertNotNull($pending->last_sent_at);
        $this->assertNotSame($code, $pending->code_hash); // tersimpan sebagai hash
    }

    public function test_verifikasi_kode_membuat_akun_dan_token(): void
    {
        $this->postJson('/api/otp/request', $this->payload())->assertCreated();

        $code = null;
        Mail::assertSent(OtpMail::class, function (OtpMail $mail) use (&$code) {
            $code = $mail->code;
            return true;
        });

        $response = $this->postJson('/api/otp/verify', [
            'email' => 'bagas@example.com',
            'code' => $code,
        ]);

        $response->assertCreated()->assertJsonStructure(['message', 'user', 'token']);

        $user = User::where('email', 'bagas@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('user', $user->role);
        $this->assertNotNull($user->email_verified_at); // OTP membuktikan email
        $this->assertDatabaseCount('otp_verifications', 0); // sekali pakai
    }

    public function test_kode_salah_menambah_attempt_dan_tidak_membuat_akun(): void
    {
        $this->postJson('/api/otp/request', $this->payload())->assertCreated();

        $this->postJson('/api/otp/verify', [
            'email' => 'bagas@example.com',
            'code' => '000000',
        ])->assertStatus(422)->assertJsonPath('message', 'Kode salah. Sisa percobaan: 4x.');

        $this->assertSame(1, OtpVerification::first()->attempts);
        $this->assertDatabaseCount('pengguna', 0);
    }

    public function test_kode_kedaluwarsa_ditolak(): void
    {
        $pending = OtpVerification::create([
            'name' => 'Bagas',
            'username' => 'bagasuji',
            'email' => 'bagas@example.com',
            'phone' => '6281234567890',
            'password' => bcrypt('rahasia123'),
            'channel' => 'email',
            'code_hash' => bcrypt('123456'),
            'attempts' => 0,
            'expires_at' => now()->subMinute(),
            'last_sent_at' => now()->subMinutes(11),
        ]);

        $this->postJson('/api/otp/verify', [
            'email' => 'bagas@example.com',
            'code' => '123456',
        ])->assertStatus(410);

        $this->assertDatabaseMissing('otp_verifications', ['id' => $pending->id]);
        $this->assertDatabaseCount('pengguna', 0);
    }

    public function test_whatsapp_dikirim_via_fonnte(): void
    {
        Http::fake([
            'api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['abc']], 200),
        ]);

        $response = $this->postJson('/api/otp/request', $this->payload(['channel' => 'wa']));

        $response->assertCreated()->assertJson(['sent' => true, 'via' => 'wa']);
        Mail::assertNothingSent();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.fonnte.com/send')
                && $request['target'] === '6281234567890'
                && $request->hasHeader('Authorization', 'token-fonnte-uji');
        });
    }

    public function test_whatsapp_gagal_menolak_pendaftaran_dengan_pesan_jelas(): void
    {
        Http::fake([
            'api.fonnte.com/*' => Http::response(['status' => false, 'reason' => 'device disconnected'], 200),
        ]);

        $response = $this->postJson('/api/otp/request', $this->payload(['channel' => 'wa']));

        $response->assertStatus(502)
            ->assertJson(['sent' => false, 'error_code' => 'device'])
            ->assertJsonPath('message', 'Perangkat WhatsApp Fonnte terputus. Buka dashboard Fonnte lalu scan ulang QR.');

        $this->assertDatabaseCount('otp_verifications', 0); // tidak ada calon akun menggantung
    }

    public function test_email_gagal_login_ditolak_dengan_pesan_jelas(): void
    {
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new \RuntimeException('Expected response code "235" but got code "535", with message "535-5.7.8 Username and Password not accepted"'));

        $response = $this->postJson('/api/otp/request', $this->payload());

        $response->assertStatus(502)
            ->assertJson(['sent' => false, 'error_code' => 'auth'])
            ->assertJsonPath('message', 'Email pengirim menolak login. Periksa MAIL_USERNAME dan App Password Gmail (16 huruf, bukan password biasa).');

        $this->assertDatabaseCount('otp_verifications', 0);
    }

    public function test_cooldown_kirim_ulang_ditegakkan_di_server(): void
    {
        $this->postJson('/api/otp/request', $this->payload())->assertCreated();

        $this->postJson('/api/otp/request', $this->payload())
            ->assertStatus(429)
            ->assertJsonStructure(['message', 'retry_after']);
    }

    public function test_channel_belum_dikonfigurasi_ditolak(): void
    {
        config(['services.fonnte.token' => '']);

        $this->postJson('/api/otp/request', $this->payload(['channel' => 'wa']))
            ->assertStatus(422)
            ->assertJson(['sent' => false, 'error_code' => 'not_configured']);

        $this->assertDatabaseCount('otp_verifications', 0);
    }

    public function test_endpoint_config_melaporkan_channel_aktif(): void
    {
        $this->getJson('/api/otp/config')
            ->assertOk()
            ->assertJson([
                'email_configured' => true,
                'wa_configured' => true,
                'demo' => false,
                'resend_seconds' => 30,
            ]);
    }

    public function test_mode_tes_lokal_menulis_kode_ke_log_bukan_ke_browser(): void
    {
        Log::spy();
        config(['mail.default' => 'log', 'services.otp.local_fallback' => true]);

        $response = $this->postJson('/api/otp/request', $this->payload());

        $response->assertCreated()
            ->assertJson(['sent' => true, 'local' => true])
            ->assertJsonMissingPath('dev_code');

        Mail::assertNothingSent();
        $this->assertDatabaseCount('otp_verifications', 1);

        // Kode hanya boleh ada di log, bukan di response.
        $code = null;
        Log::shouldHaveReceived('warning')->withArgs(function ($message, $context) use (&$code) {
            $code = $context['code'] ?? null;

            return str_contains((string) $message, 'OTP MODE TES LOKAL')
                && is_string($code) && preg_match('/^\d{6}$/', $code) === 1;
        });

        $this->assertNotNull($code);
        $this->assertStringNotContainsString((string) $code, $response->getContent());
    }

    public function test_mode_tes_lokal_membuat_channel_dianggap_aktif(): void
    {
        config(['mail.default' => 'log', 'services.fonnte.token' => '', 'services.otp.local_fallback' => true]);

        $this->getJson('/api/otp/config')
            ->assertOk()
            ->assertJson([
                'email_configured' => true,
                'wa_configured' => true,
                'local_fallback' => true,
                'delivery' => 'log',
            ]);
    }

    public function test_alur_pendaftaran_web_memakai_otp_asli(): void
    {
        $this->post('/register', $this->payload())->assertRedirect('/register/otp');

        $code = null;
        Mail::assertSent(OtpMail::class, function (OtpMail $mail) use (&$code) {
            $code = $mail->code;
            return true;
        });

        $this->post('/register/otp', ['code' => $code])->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('pengguna', ['email' => 'bagas@example.com', 'role' => 'user']);
    }

    public function test_halaman_register_web_dapat_dibuka(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Kirim Kode Verifikasi (OTP) ke');
    }

    public function test_halaman_pengaturan_otp_dapat_dibuka_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'adminuji', 'is_active' => true]);

        $this->actingAs($admin)
            ->get('/panel/admin/otp')
            ->assertOk()
            ->assertSee('Pengaturan Pengiriman OTP')
            ->assertSee('Kirim Tes Sekarang');
    }

    public function test_admin_menyimpan_kredensial_dan_secret_lama_tetap_aman(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'adminuji', 'is_active' => true]);

        $this->actingAs($admin)->put('/panel/admin/otp', [
            'email_enabled' => '1',
            'wa_enabled' => '1',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_username' => 'pengirim@gmail.com',
            'mail_password' => 'abcd efgh ijkl mnop',
            'mail_encryption' => 'tls',
            'mail_from_name' => 'Batik Binar',
            'fonnte_token' => 'token-abc',
        ])->assertRedirect()->assertSessionHas('success');

        // App Password disimpan tanpa spasi; token WA tersimpan.
        $this->assertSame('abcdefghijklmnop', OtpSetting::get('mail_password'));
        $this->assertSame('token-abc', OtpSetting::get('fonnte_token'));

        // Simpan form WhatsApp tanpa mengisi password: password lama TIDAK boleh hilang.
        $this->actingAs($admin)->put('/panel/admin/otp', [
            'email_enabled' => '1',
            'wa_enabled' => '1',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_username' => 'pengirim@gmail.com',
            'mail_encryption' => 'tls',
            'mail_from_name' => 'Batik Binar',
            'fonnte_token' => '',
        ])->assertRedirect();

        $this->assertSame('abcdefghijklmnop', OtpSetting::get('mail_password'));
        $this->assertSame('token-abc', OtpSetting::get('fonnte_token'));
    }
}
