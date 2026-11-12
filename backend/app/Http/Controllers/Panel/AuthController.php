<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\AuthController as MainAuthController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ALIAS login satu pintu: /panel/login kini mengarah ke /login utama.
 * Semua role (user, admin, superadmin) login di satu halaman; role dibaca
 * dari akun terdaftar lalu diarahkan otomatis (redirectByRole).
 * Controller ini dipertahankan agar URL lama tidak 404.
 */
class AuthController extends Controller
{
    public function showLogin()
    {
        return redirect()->route('login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate(['email' => 'required|string', 'password' => 'required']);

        $login = trim($cred['email']);
        $user = User::where('email', $login)->orWhere('username', $login)->first();

        if ($user && $user->isBanned()) {
            return back()->with('error', 'Akun di-BANNED.' . ($user->ban_reason ? ' Alasan: ' . $user->ban_reason : ''))->withInput();
        }
        if ($user && ! $user->is_active) {
            return back()->with('error', 'Akun dinonaktifkan.')->withInput();
        }

        if ($user && Auth::attempt(['email' => $user->email, 'password' => $cred['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            $token = Auth::user()->createToken('batik-binar-web')->plainTextToken;
            return redirect()->intended(MainAuthController::redirectByRole(Auth::user()))->with('web_token', $token);
        }

        return back()->with('error', 'Email/username atau password salah.')->withInput();
    }
}
