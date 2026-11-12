<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Usage: ->middleware('role:admin,superadmin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            // tamu area panel -> gerbang panel; tamu web utama -> login user
            if ($request->is('panel*')) {
                return redirect()->route('panel.login')->with('error', 'Silakan login panel dulu.');
            }
            return redirect()->route('login')->with('error', 'Silakan login dulu.');
        }

        // tendang akun yang di-banned / dinonaktifkan di tengah sesi
        if ($user->isBanned() || ! $user->is_active) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Akun diblokir.'], 403);
            }
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Akun Anda diblokir.');
        }

        if (! in_array($user->role, $roles, true)) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Role tidak diizinkan.'], 403);
            }
            // Satu sesi per browser: bila akun yang login rolenya tidak cocok
            // (mis. sesi user membuka halaman panel), kembalikan ke dashboard
            // dengan pesan ganti akun — login ulang mengganti sesi lama.
            // (Via route('dashboard') agar pesan flash tampil dalam 1 hop;
            // via /login pesannya hilang karena guest-middleware memantul lagi.)
            if ($request->is('panel*')) {
                $home = match ($user->role) {
                    'superadmin' => 'superadmin.dashboard',
                    'admin' => 'admin.dashboard',
                    default => 'user.dashboard',
                };
                return redirect()->route($home)->with('error', 'Sesi Anda login sebagai ' . $user->role . '. Logout lalu login sebagai ' . implode('/', $roles) . ' untuk membuka halaman ini.');
            }
            abort(403, 'Akses ditolak untuk role: ' . $user->role);
        }

        return $next($request);
    }
}
