<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Motif;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return match ($user->role) {
            'superadmin' => redirect()->route('superadmin.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('user.dashboard'),
        };
    }

    public function user()
    {
        // Satu pintu: admin/superadmin yang nyasar ke dashboard user
        // dikembalikan ke panel masing-masing (lihat ikon di navbar native).
        $role = Auth::user()->role;
        if ($role === 'superadmin') return redirect()->route('superadmin.dashboard');
        if ($role === 'admin') return redirect()->route('admin.dashboard');
        $products = Product::where('status', 'aktif')->latest()->take(6)->get();
        $galleries = Gallery::where('is_published', true)->latest()->take(4)->get();
        $motifs = Motif::where('is_published', true)->latest()->take(3)->get();
        $myOrders = Order::with('product')->where('user_id', Auth::id())->latest()->take(5)->get();
        $stats = [
            'aktif' => Order::where('user_id', Auth::id())->whereIn('status', Order::STATUS_AKTIF)->count(),
            'riwayat' => Order::where('user_id', Auth::id())->whereIn('status', Order::STATUS_RIWAYAT)->count(),
            'wishlist' => \App\Models\Wishlist::where('user_id', Auth::id())->count(),
        ];
        return view('dashboards.user', compact('products', 'galleries', 'motifs', 'myOrders', 'stats'));
    }

    public function admin()
    {
        $stats = [
            'products' => Product::count(),
            'galleries' => Gallery::count(),
            'motifs' => Motif::count(),
            'orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
        ];
        $latestOrders = Order::with(['user', 'product'])->latest()->take(5)->get();
        return view('dashboards.admin', compact('stats', 'latestOrders'));
    }

    public function superadmin()
    {
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_superadmins' => User::where('role', 'superadmin')->count(),
            'products' => Product::count(),
            'orders' => Order::count(),
        ];
        $latestUsers = User::latest()->take(5)->get();
        return view('dashboards.superadmin', compact('stats', 'latestUsers'));
    }
}
