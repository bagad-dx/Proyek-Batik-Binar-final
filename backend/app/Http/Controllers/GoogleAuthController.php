<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FirebaseTokenVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Login/Daftar via Google (Firebase Authentication).
 * Web: POST /auth/google {idToken} -> JSON {redirect_url}
 * API: POST /api/auth/google {idToken} -> JSON {user, token}
 */
class GoogleAuthController extends Controller
{
    public function loginWeb(Request $request, FirebaseTokenVerifier $verifier)
    {
        $request->validate(['idToken' => 'required|string']);

        try {
            $user = $this->findOrCreate($verifier, $request->idToken);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

        if ($msg = $this->blockedMessage($user)) {
            return response()->json(['message' => $msg], 403);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'redirect_url' => AuthController::redirectByRole($user),
            'api_token' => $user->createToken('batik-binar-web')->plainTextToken,
        ]);
    }

    public function loginApi(Request $request, FirebaseTokenVerifier $verifier)
    {
        $request->validate(['idToken' => 'required|string']);

        try {
            $user = $this->findOrCreate($verifier, $request->idToken);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

        if ($user->isBanned()) {
            return response()->json(['message' => 'Akun di-BANNED.' . ($user->ban_reason ? ' Alasan: ' . $user->ban_reason : '')], 403);
        }
        if (! $user->is_active) {
            return response()->json(['message' => 'Akun dinonaktifkan.'], 403);
        }

        return response()->json([
            'message' => 'Login Google berhasil',
            'user' => $user,
            'token' => $user->createToken('batik-binar-google')->plainTextToken,
        ]);
    }

    protected function blockedMessage(User $user): ?string
    {
        if ($user->isBanned()) {
            return 'Akun di-BANNED.' . ($user->ban_reason ? ' Alasan: ' . $user->ban_reason : '');
        }
        if (! $user->is_active) return 'Akun dinonaktifkan. Hubungi superadmin.';
        return null;
    }

    protected function findOrCreate(FirebaseTokenVerifier $verifier, string $idToken): User
    {
        $projectId = config('services.firebase.project_id');
        if (! $projectId) throw new \RuntimeException('FIREBASE_PROJECT_ID belum diisi di .env.');

        $claims = $verifier->verify($idToken, $projectId);

        $email = $claims['email'] ?? null;
        if (! $email || ($claims['email_verified'] ?? false) !== true) {
            throw new \RuntimeException('Email Google belum terverifikasi.');
        }

        // 1. cocok via google_id, 2. via email (tautkan), 3. buat baru
        $user = User::where('google_id', $claims['sub'])->first();
        if (! $user) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update([
                    'google_id' => $claims['sub'],
                    'avatar' => $user->avatar ?? ($claims['picture'] ?? null),
                ]);
            }
        }
        if (! $user) {
            $base = strtolower(preg_replace('/[^a-z0-9]+/i', '', explode('@', $email)[0])) ?: 'user';
            $username = $base;
            for ($i = 1; User::where('username', $username)->exists(); $i++) {
                $username = $base . $i;
            }
            $user = User::create([
                'name' => $claims['name'] ?? $username,
                'username' => substr($username, 0, 30),
                'email' => $email,
                'password' => Str::random(40), // login hanya via Google
                'role' => 'user',
                'avatar' => $claims['picture'] ?? null,
                'google_id' => $claims['sub'],
                'is_active' => true,
            ]);
        }
        return $user->fresh();
    }
}
