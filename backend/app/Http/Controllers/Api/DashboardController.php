<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Wishlist;
use Illuminate\Http\Request;

/** Ringkasan dasbor pembeli: pesanan aktif, riwayat, wishlist. */
class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $uid = $request->user()->id;
        Order::expireOverdueFor($uid);

        $aktif = Order::where('user_id', $uid)->whereIn('status', Order::STATUS_AKTIF)->count();
        $riwayat = Order::where('user_id', $uid)->whereIn('status', Order::STATUS_RIWAYAT)->count();
        $terbaru = Order::with(['product', 'details.product'])->where('user_id', $uid)->latest()->take(5)->get();
        $wishlist = Wishlist::where('user_id', $uid)->count();
        $keranjang = Cart::where('user_id', $uid)->sum('jumlah');

        return response()->json([
            'user' => $request->user(),
            'pesanan_aktif' => $aktif,
            'riwayat_pesanan' => $riwayat,
            'wishlist' => $wishlist,
            'keranjang' => (int) $keranjang,
            'pesanan_terbaru' => $terbaru,
        ]);
    }
}
