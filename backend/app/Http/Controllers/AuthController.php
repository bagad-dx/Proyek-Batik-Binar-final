<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\OtpVerification;
use App\Models\OtpSetting;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }

    public function showRegister()
    {
        return view('auth.register', [
            'emailReady' => OtpService::channelAvailable('email'),
            'waReady' => OtpService::channelAvailable('wa'),
            'localFallback' => OtpSetting::localFallback(),
        ]);
    }

    /** Login WEB SATU PINTU (/login): user, admin, superadmin.
     *  Role dibaca dari akun yang sudah terdaftar (kolom role di tabel
     *  pengguna): admin/superadmin otomatis diarahkan ke panel, sisanya
     *  (termasuk email baru via Google) masuk sebagai user biasa. */
    protected function findLoginUser(string $login): ?User
    {
        return User::where('email', $login)->orWhere('username', $login)->first();
    }

    /** Pesan penolakan jika akun banned / nonaktif. Null = boleh lanjut. */
    protected function blockedMessage(?User $user): ?string
    {
        if ($user && $user->isBanned()) {
            $msg = 'Akun di-BANNED.' . ($user->ban_reason ? ' Alasan: ' . $user->ban_reason : '') . ' Hubungi superadmin.';
            return $msg;
        }
        if ($user && ! $user->is_active) {
            return 'Akun dinonaktifkan. Hubungi superadmin.';
        }
        return null;
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);

        $user = $this->findLoginUser(trim($cred['email']));
        if ($msg = $this->blockedMessage($user)) {
            return back()->with('error', $msg)->withInput();
        }

        if ($user && Auth::attempt(['email' => $user->email, 'password' => $cred['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            // Samakan token native (localStorage) dengan sesi web agar halaman
            // native tidak menampilkan akun lama yang masih tersimpan di browser.
            $token = Auth::user()->createToken('batik-binar-web')->plainTextToken;
            return redirect()->intended($this->redirectByRole(Auth::user()))->with('web_token', $token);
        }

        return back()->with('error', 'Email/username atau password salah.')->withInput();
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'username' => 'required|string|min:3|max:30|alpha_dash|unique:pengguna,username',
            'email' => 'required|email|max:255|unique:pengguna,email',
            'phone' => ['required', 'string', 'min:9', 'max:20', function (string $attribute, mixed $value, \Closure $fail) {
                if (! OtpService::isValidPhone((string) $value)) {
                    $fail('Nomor WhatsApp tidak valid. Gunakan format 08xxxxxxxxxx.');
                }
            }],
            'password' => ['required', 'confirmed', Password::min(6)],
            'channel' => 'nullable|in:email,wa',
        ]);

        // Langkah 1: simpan calon akun + kirim kode OTP 6 digit ke
        // Email/Gmail atau WhatsApp. Akun BARU dibuat setelah kode benar.
        $channel = $data['channel'] ?? 'email';
        $email = strtolower(trim($data['email']));
        $phone = OtpService::normalizePhone($data['phone']);

        if (! OtpService::channelAvailable($channel)) {
            return back()->withInput()->with('error', 'Verifikasi lewat ' . ($channel === 'wa' ? 'WhatsApp' : 'Email') . ' belum aktif. Pilih channel lain atau hubungi admin.');
        }

        $existing = OtpVerification::where('email', $email)->first();
        if ($existing && $existing->last_sent_at) {
            $wait = max(0, OtpService::RESEND_SECONDS - (int) $existing->last_sent_at->diffInSeconds(now()));
            if ($wait > 0) {
                return back()->withInput()->with('error', "Tunggu {$wait} detik sebelum meminta kode lagi.");
            }
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
            $pending->delete();

            return back()->withInput()->with('error', $result['error'] ?: 'Kode OTP gagal dikirim. Coba lagi.');
        }

        $pending->update(['last_sent_at' => now()]);

        session([
            'otp_email' => $email,
            'otp_channel' => $channel,
        ]);

        $dest = $channel === 'wa'
            ? 'WhatsApp ' . OtpService::maskPhone($phone)
            : 'email ' . OtpService::maskEmail($email);

        $msg = ! empty($result['local'])
            ? 'MODE TES LOKAL aktif: kode OTP ditulis ke storage/logs/otp-log.log (tidak dikirim ke email/WhatsApp).'
            : "Kode OTP dikirim ke {$dest}. Masukkan 6 digit kode.";

        return redirect()->route('register.otp')->with('success', $msg);
    }

    /** Form verifikasi OTP (langkah 2 pendaftaran web). */
    public function showOtpForm()
    {
        if (! session('otp_email')) {
            return redirect()->route('register')->with('error', 'Isi form pendaftaran dulu.');
        }

        $pending = OtpVerification::where('email', session('otp_email'))->first();
        $channel = session('otp_channel', $pending->channel ?? 'email');

        return view('auth.verify-otp', [
            'emailReady' => OtpService::channelAvailable('email'),
            'waReady' => OtpService::channelAvailable('wa'),
            'localFallback' => OtpSetting::localFallback(),
            'channel' => $channel,
            'destMasked' => $channel === 'wa'
                ? OtpService::maskPhone((string) ($pending->phone ?? ''))
                : OtpService::maskEmail((string) session('otp_email')),
            'resendSeconds' => OtpService::RESEND_SECONDS,
            'cooldown' => $pending && $pending->last_sent_at
                ? max(0, OtpService::RESEND_SECONDS - (int) $pending->last_sent_at->diffInSeconds(now()))
                : 0,
            'pending' => $pending,
        ]);
    }

    /** Verifikasi kode OTP web -> buat akun -> login otomatis. */
    public function verifyOtp(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);
        $email = session('otp_email');
        if (! $email) {
            return redirect()->route('register')->with('error', 'Sesi OTP habis. Isi form pendaftaran lagi.');
        }

        return DB::transaction(function () use ($email, $request) {
            $pending = OtpVerification::where('email', $email)->lockForUpdate()->first();
            if (! $pending) {
                return redirect()->route('register')->with('error', 'Tidak ada pendaftaran menunggu. Isi form lagi.');
            }
            if ($pending->isExpired()) {
                $pending->delete();
                return redirect()->route('register')->with('error', 'Kode kedaluwarsa. Isi form pendaftaran lagi.');
            }
            if ($pending->isLocked()) {
                $pending->delete();
                return redirect()->route('register')->with('error', 'Terlalu banyak salah (5x). Isi form pendaftaran lagi.');
            }
            if (User::where('email', $pending->email)->exists()) {
                $pending->delete();
                session()->forget('otp_email');
                return redirect()->route('login')->with('success', 'Email ini sudah terdaftar. Silakan masuk.');
            }
            if (! Hash::check($request->code, $pending->code_hash)) {
                $pending->increment('attempts');
                $sisa = OtpVerification::MAX_ATTEMPTS - $pending->fresh()->attempts;
                return back()->with('error', "Kode salah. Sisa percobaan: {$sisa}x.");
            }

            $user = User::create([
                'name' => $pending->name,
                'username' => $pending->username,
                'email' => $pending->email,
                'phone' => $pending->phone,
                'password' => $pending->password, // sudah di-hash saat request
                'role' => 'user', // register publik selalu user
                'email_verified_at' => now(), // OTP membuktikan email benar
            ]);
            $pending->delete();
            session()->forget(['otp_email', 'otp_channel']);

            Auth::login($user);

            $token = $user->createToken('batik-binar-web')->plainTextToken;

            return redirect()->route('dashboard')->with('success', 'Verifikasi berhasil. Selamat datang!')->with('web_token', $token);
        });
    }

    /** Kirim ulang kode OTP web (tombol di halaman verifikasi). */
    public function resendOtp(Request $request)
    {
        $email = session('otp_email');
        if (! $email) {
            return redirect()->route('register')->with('error', 'Sesi OTP habis. Isi form pendaftaran lagi.');
        }
        $pending = OtpVerification::where('email', $email)->first();
        if (! $pending) {
            return redirect()->route('register')->with('error', 'Tidak ada pendaftaran menunggu. Isi form lagi.');
        }

        if ($pending->last_sent_at) {
            $wait = max(0, OtpService::RESEND_SECONDS - (int) $pending->last_sent_at->diffInSeconds(now()));
            if ($wait > 0) {
                return back()->with('error', "Tunggu {$wait} detik sebelum mengirim ulang.");
            }
        }

        $channel = $request->validate(['channel' => 'nullable|in:email,wa'])['channel'] ?? $pending->channel;

        if (! OtpService::channelAvailable($channel)) {
            return back()->with('error', 'Verifikasi lewat ' . ($channel === 'wa' ? 'WhatsApp' : 'Email') . ' belum aktif.');
        }

        $code = OtpService::generateCode();
        $result = OtpService::send($channel, $pending->name, $pending->email, $pending->phone, $code);

        if (! $result['sent']) {
            return back()->with('error', $result['error'] ?: 'Kode OTP gagal dikirim ulang. Coba lagi.');
        }

        $pending->update([
            'channel' => $channel,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(OtpVerification::TTL_MINUTES),
            'last_sent_at' => now(),
        ]);

        session(['otp_channel' => $channel]);

        return back()->with('success', ! empty($result['local'])
            ? 'MODE TES LOKAL: kode OTP baru ditulis ke storage/logs/otp-log.log.'
            : 'Kode OTP baru dikirim. Masukkan 6 digit kode.');
    }

    public function logout(Request $request)
    {
        // Cabut token web milik sesi ini agar halaman native ikut logout.
        try { $request->user()->tokens()->where('name', 'batik-binar-web')->delete(); } catch (\Throwable $e) {}
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Berhasil logout.')->with('clear_token', true);
    }

    /** Edit data profil via form Blade (user): nama, gmail, telepon, alamat, password opsional. */
    public function updateProfileWeb(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => ['required', 'email', \Illuminate\Validation\Rule::unique('pengguna')->ignore($user->id)],
            'phone' => 'required|string|min:9|max:20',
            'alamat' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:6|confirmed',
        ]);
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'];
        $user->alamat = $data['alamat'] ?? null;
        if (! empty($data['password'])) $user->password = $data['password'];
        $user->save();
        return back()->with('success', 'Profil diperbarui.');
    }

    /** Upload foto profil via form Blade (user). */
    public function updateAvatarWeb(Request $request)
    {
        $request->validate(['avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);
        $user = Auth::user();
        $old = $user->avatar;
        $user->avatar = $request->file('avatar')->store('avatars', 'public');
        $user->save();
        if ($old && ! str_starts_with($old, 'http') && \Illuminate\Support\Facades\Storage::disk('public')->exists($old)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($old);
        }
        return back()->with('success', 'Foto profil diperbarui.');
    }

    public static function redirectByRole(User $user): string
    {
        return match ($user->role) {
            'superadmin' => route('superadmin.dashboard'),
            'admin' => route('admin.dashboard'),
            default => route('user.dashboard'),
        };
    }

    /**
     * Jembatan sesi web -> token native (GET /api/session-token, middleware web).
     * Dipanggil halaman native se-origin (fetch credentials:include): bila ada
     * sesi web, token native diselaraskan ke akun sesi — menyembuhkan token
     * basi (mis. tertinggal superadmin padahal sesi web-nya user).
     */
    public function sessionToken(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (! $user) return response()->json(['token' => null, 'user' => null]);
        $user->tokens()->where('name', 'batik-binar-web')->delete();
        return response()->json([
            'token' => $user->createToken('batik-binar-web')->plainTextToken,
            'user' => $user,
        ]);
    }
}
