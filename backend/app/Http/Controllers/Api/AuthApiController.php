<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthApiController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'username' => 'nullable|string|min:3|max:30|alpha_dash|unique:pengguna,username',
            'email' => 'required|email|unique:pengguna,email',
            'phone' => 'required|string|min:9|max:20',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        // username opsional (form native tidak punya): buat dari email
        if (empty($data['username'])) {
            $base = strtolower(preg_replace('/[^a-z0-9]+/i', '', explode('@', $data['email'])[0])) ?: 'user';
            $username = substr($base, 0, 24);
            for ($i = 1; User::where('username', $username)->exists(); $i++) {
                $username = substr($base, 0, 24) . $i;
            }
            $data['username'] = $username;
        }

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        $token = $user->createToken('batik-binar')->plainTextToken;

        return response()->json(['message' => 'Registrasi berhasil', 'user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $request->validate(['email' => 'required|string', 'password' => 'required']);

        $login = trim($request->email);
        $user = User::where('email', $login)->orWhere('username', $login)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email/username atau password salah.'], 401);
        }
        if ($user->isBanned()) {
            return response()->json(['message' => 'Akun di-BANNED.' . ($user->ban_reason ? ' Alasan: ' . $user->ban_reason : '')], 403);
        }
        if (! $user->is_active) {
            return response()->json(['message' => 'Akun dinonaktifkan.'], 403);
        }

        $token = $user->createToken('batik-binar')->plainTextToken;

        return response()->json(['message' => 'Login berhasil', 'user' => $user, 'token' => $token]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /** Edit profil (nama, gmail, telepon, alamat, password opsional). */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
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
        if (! empty($data['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
        }
        $user->save();
        return response()->json(['message' => 'Profil diperbarui.', 'user' => $user->fresh()]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }

    /** Upload foto profil (multipart `avatar`: jpg/png/webp, maks 2MB). */
    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);
        $user = $request->user();
        $old = $user->avatar;
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();
        if ($old && ! str_starts_with($old, 'http') && \Illuminate\Support\Facades\Storage::disk('public')->exists($old)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($old);
        }
        return response()->json(['message' => 'Foto profil diperbarui.', 'user' => $user->fresh()]);
    }
}
