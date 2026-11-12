<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        \App\Models\Order::expireOverdueFor(Auth::id());
        $orders = Order::with('product')->where('user_id', Auth::id())->latest()->paginate(10);
        return view('user.orders.index', compact('orders'));
    }

    /** Pembeli membatalkan pesanannya sendiri (hanya selagi pending). */
    public function cancel(Order $order)
    {
        abort_unless((int) $order->user_id === (int) Auth::id(), 403, 'Bukan pesanan Anda.');
        if ($order->status !== 'pending') {
            return back()->with('error', 'Hanya pesanan berstatus pending yang bisa dibatalkan.');
        }
        $order->update(['status' => 'batal']);
        if ($order->product) $order->product->increment('stok', $order->jumlah);
        return back()->with('success', 'Pesanan dibatalkan, stok dikembalikan.');
    }

    public function create(Product $product)
    {
        abort_unless($product->status === 'aktif', 404);
        return view('user.orders.create', compact('product'));
    }

    public function store(Request $request, Product $product, \App\Services\MidtransService $midtrans)
    {
        $data = $request->validate([
            'jumlah' => 'required|integer|min:1|max:' . max($product->stok, 1),
            'alamat' => 'required|string|min:10',
            'no_hp' => 'required|string|max:20',
            'payment_method' => 'required|in:transfer,cod,ewallet,qris,midtrans',
            'pay_channel' => 'nullable|in:transfer,cod,ewallet,qris,kartu',
            'bank' => 'nullable|in:bca,bni,bri,permata',
            'ewallet' => 'nullable|in:gopay,shopeepay',
            'store' => 'nullable|in:alfamart,indomaret',
            'expedition' => 'required|in:jne,jnt,pos,sicepat,gosend',
            'catatan' => 'nullable|string',
        ]);

        if ($product->stok < $data['jumlah']) {
            return back()->with('error', 'Stok tidak mencukupi.')->withInput();
        }

        $shipping = Order::EXPEDITIONS[$data['expedition']]['ongkir'];

        $order = Order::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'jumlah' => $data['jumlah'],
            'total_harga' => $product->harga * $data['jumlah'],
            'alamat' => $data['alamat'],
            'no_hp' => $data['no_hp'],
            'payment_method' => 'midtrans',
            'expedition' => $data['expedition'],
            'shipping_cost' => $shipping,
            'batas_bayar' => Order::batasBayarDefault(),
            'catatan' => ($data['catatan'] ?? '') . (($data['pay_channel'] ?? '') ? ' [via:' . $data['pay_channel'] . (isset($data['bank']) ? ':' . $data['bank'] : '') . (isset($data['ewallet']) ? ':' . $data['ewallet'] : '') . (isset($data['store']) ? ':' . $data['store'] : '') . ']' : ''),
        ]);

        $product->decrement('stok', $data['jumlah']);

        $order->details()->create([
            'product_id' => $product->id,
            'jumlah' => $data['jumlah'],
            'harga_satuan' => $product->harga,
            'subtotal' => $product->harga * $data['jumlah'],
        ]);

        kirim_notifikasi(Auth::id(), 'Pesanan #' . $order->id . ' dibuat',
            $product->nama . ' x' . $data['jumlah'] . ' — silakan selesaikan pembayaran Midtrans.',
            'pesanan', 'pesanan', $order->id);

        $channel = $data['pay_channel'] ?? 'qris';
        if ($channel === 'kartu') {
            // kartu butuh form 3DS -> tetap via Snap terfilter
            try {
                $midtrans->snapToken($order->fresh(), \App\Services\MidtransService::enabledFor('kartu'));
                return redirect()->route('user.orders.pay', $order);
            } catch (\RuntimeException $e) {
                return redirect()->route('user.orders.pay', $order)
                    ->with('error', $e->getMessage() . ' Coba lagi nanti.');
            }
        }

        try {
            $direct = $midtrans->chargeDirect($order->fresh(), $channel, [
                'bank' => $data['bank'] ?? null,
                'ewallet' => $data['ewallet'] ?? null,
                'store' => $data['store'] ?? null,
            ]);
            return redirect()->route('user.orders.pay', $order)->with('direct_pay', $direct);
        } catch (\RuntimeException $e) {
            return redirect()->route('user.orders.pay', $order)
                ->with('error', $e->getMessage() . ' Coba lagi nanti.');
        }
    }

    /** Halaman bayar: kode/QR langsung (atau Snap untuk kartu). */
    public function pay(Order $order)
    {
        abort_unless((int) $order->user_id === (int) Auth::id(), 403);
        abort_unless($order->payment_method === 'midtrans' && $order->status === 'pending', 404);
        return view('user.orders.pay', compact('order'));
    }

    /** Data bayar susulan (JSON) untuk halaman pay. */
    public function paymentData(Order $order, \App\Services\MidtransService $midtrans)
    {
        abort_unless((int) $order->user_id === (int) Auth::id(), 403);
        abort_unless($order->status === 'pending' && ! $order->paid_at, 422, 'Pesanan sudah dibayar/diproses.');
        $display = $midtrans->fetchPaymentDisplay($order);
        if (! $display) return response()->json(['message' => 'Data pembayaran belum tersedia.'], 422);
        return response()->json($display);
    }

    // Katalog login (tetap untuk kompatibilitas)
    public function catalog(Request $request)
    {
        return $this->shop($request, 'user.catalog');
    }

    // Belanja publik (tamu bisa lihat, beli diarahkan login/checkout)
    public function shop(Request $request, string $view = 'user.shop')
    {
        $q = Product::with('category')->where('status', 'aktif')->latest();
        if ($request->filled('search')) {
            $q->where(fn ($w) => $w->where('nama', 'like', '%' . $request->search . '%')
                ->orWhere('motif', 'like', '%' . $request->search . '%'));
        }
        if ($request->filled('category_id')) $q->where('category_id', $request->category_id);
        $products = $q->paginate(9)->withQueryString();
        $categories = Category::all();
        return view($view, compact('products', 'categories'));
    }

    // Detail produk publik
    public function show(Product $product)
    {
        abort_unless($product->status === 'aktif', 404);
        $related = Product::where('status', 'aktif')->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)->take(3)->get();
        $shopeeUrl = $product->shopee_url ?: \App\Models\ChatSetting::get('shopee_store_url');
        return view('user.show', compact('product', 'related', 'shopeeUrl'));
    }
}
