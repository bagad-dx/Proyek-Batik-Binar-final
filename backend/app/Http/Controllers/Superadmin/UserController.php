<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = User::latest();
        if ($request->filled('role')) $q->where('role', $request->role);
        if ($request->filled('status')) {
            if ($request->status === 'banned') $q->whereNotNull('banned_at');
            elseif ($request->status === 'aktif') $q->whereNull('banned_at')->where('is_active', true);
            elseif ($request->status === 'nonaktif') $q->whereNull('banned_at')->where('is_active', false);
        }
        if ($request->filled('search')) {
            $q->where(fn ($w) => $w->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('username', 'like', '%' . $request->search . '%'));
        }
        $users = $q->paginate(10)->withQueryString();
        return view('superadmin.users.index', compact('users'));
    }

    public function create()
    {
        return view('superadmin.users.form', ['user' => new User()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'username' => 'required|string|min:3|max:30|alpha_dash|unique:pengguna,username',
            'email' => 'required|email|unique:pengguna,email',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(User::ROLES)],
            'password' => 'required|string|min:6|confirmed',
            'is_active' => 'boolean',
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);
        User::create($data);
        return redirect()->route('superadmin.users.index')->with('success', 'User/admin berhasil dibuat.');
    }

    public function edit(User $user)
    {
        return view('superadmin.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash', Rule::unique('pengguna')->ignore($user->id)],
            'email' => ['required', 'email', Rule::unique('pengguna')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(User::ROLES)],
            'password' => 'nullable|string|min:6|confirmed',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        // cegah superadmin terakhir menonaktifkan dirinya sendiri
        if ((int) $user->id === (int) auth()->id() && ($data['role'] !== 'superadmin' || ! $data['is_active'])) {
            return back()->with('error', 'Tidak bisa mengubah role/status akun sendiri.')->withInput();
        }

        $user->update($data);
        return redirect()->route('superadmin.users.index')->with('success', 'Data user diupdate.');
    }

    public function destroy(User $user)
    {
        if ((int) $user->id === (int) auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }
        $user->delete();
        return back()->with('success', 'User dihapus.');
    }

    public function toggleActive(User $user)
    {
        if ((int) $user->id === (int) auth()->id()) {
            return back()->with('error', 'Tidak bisa menonaktifkan akun sendiri.');
        }
        $user->update(['is_active' => ! $user->is_active]);
        return back()->with('success', 'Status user diubah.');
    }

    public function showBan(User $user)
    {
        if ((int) $user->id === (int) auth()->id()) {
            return back()->with('error', 'Tidak bisa mem-banned akun sendiri.');
        }
        return view('superadmin.users.ban', compact('user'));
    }

    public function ban(Request $request, User $user)
    {
        if ((int) $user->id === (int) auth()->id()) {
            return back()->with('error', 'Tidak bisa mem-banned akun sendiri.');
        }
        $data = $request->validate(['ban_reason' => 'required|string|max:255']);
        $user->update(['banned_at' => now(), 'ban_reason' => $data['ban_reason'], 'is_active' => false]);
        // cabut semua token API agar langsung terblokir
        $user->tokens()->delete();
        return redirect()->route('superadmin.users.index')->with('success', "Akun {$user->username} di-BANNED.");
    }

    public function unban(User $user)
    {
        $user->update(['banned_at' => null, 'ban_reason' => null, 'is_active' => true]);
        return back()->with('success', "Banned {$user->username} dicabut, akun aktif kembali.");
    }
}
