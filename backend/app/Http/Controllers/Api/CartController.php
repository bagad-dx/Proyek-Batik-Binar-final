<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $items = Cart::with('product.category')->where('user_id', $request->user()->id)->latest()->get();
        $subtotal = $items->sum(fn ($i) => $i->product ? $i->product->harga * $i->jumlah : 0);
        return response()->json(['items' => $items, 'count' => $items->sum('jumlah'), 'subtotal' => $subtotal]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:produk,id',
            'jumlah' => 'nullable|integer|min:1|max:99',
        ]);
        $product = Product::findOrFail($data['product_id']);
        abort_unless($product->status === 'aktif', 404);
        $item = Cart::firstOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $product->id],
            ['jumlah' => 0]
        );
        $item->increment('jumlah', $data['jumlah'] ?? 1);
        return response()->json(['message' => 'Masuk keranjang.', 'item' => $item->fresh()->load('product')], 201);
    }

    public function update(Request $request, Cart $cart)
    {
        abort_unless((int) $cart->user_id === (int) $request->user()->id, 403);
        $data = $request->validate(['jumlah' => 'required|integer|min:1|max:99']);
        $cart->update(['jumlah' => $data['jumlah']]);
        return response()->json(['message' => 'Jumlah diubah.', 'item' => $cart->fresh()->load('product')]);
    }

    public function destroy(Request $request, Cart $cart)
    {
        abort_unless((int) $cart->user_id === (int) $request->user()->id, 403);
        $cart->delete();
        return response()->json(['message' => 'Dihapus dari keranjang.']);
    }

    public function clear(Request $request)
    {
        Cart::where('user_id', $request->user()->id)->delete();
        return response()->json(['message' => 'Keranjang dikosongkan.']);
    }

    /** Checkout isi keranjang (semua atau yg dicentang) -> N pesanan. */
    public function checkout(Request $request)
    {
        $data = $request->validate([
            'cart_ids' => 'nullable|array',
            'cart_ids.*' => 'integer',
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

        $q = Cart::with('product')->where('user_id', $request->user()->id);
        if (! empty($data['cart_ids'])) $q->whereIn('id', $data['cart_ids']);
        $items = $q->get();
        if ($items->isEmpty()) return response()->json(['message' => 'Tidak ada barang terpilih.'], 422);

        foreach ($items as $item) {
            if (! $item->product || $item->product->status !== 'aktif' || $item->product->stok < $item->jumlah) {
                return response()->json(['message' => 'Stok "' . ($item->product->nama ?? '-') . '" tidak mencukupi.'], 422);
            }
        }

        $shipping = Order::EXPEDITIONS[$data['expedition']]['ongkir'];
        $orders = [];
        $snaps = [];
        $directs = [];
        $midtransError = null;
        $svc = app(\App\Services\MidtransService::class);

        foreach ($items as $item) {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'product_id' => $item->product_id,
                'jumlah' => $item->jumlah,
                'total_harga' => $item->product->harga * $item->jumlah,
                'alamat' => $data['alamat'],
                'no_hp' => $data['no_hp'],
                'payment_method' => 'midtrans',
                'expedition' => $data['expedition'],
                'shipping_cost' => $shipping,
                'batas_bayar' => Order::batasBayarDefault(),
                'catatan' => ($data['catatan'] ?? '') . (isset($data['pay_channel']) ? ' [via:' . $data['pay_channel'] . (isset($data['bank']) ? ':' . $data['bank'] : '') . (isset($data['ewallet']) ? ':' . $data['ewallet'] : '') . (isset($data['store']) ? ':' . $data['store'] : '') . ']' : ''),
                'status' => 'pending',
            ]);
            $item->product->decrement('stok', $item->jumlah);
            $order->details()->create([
                'product_id' => $item->product_id,
                'jumlah' => $item->jumlah,
                'harga_satuan' => $item->product->harga,
                'subtotal' => $item->product->harga * $item->jumlah,
            ]);
            $orders[] = $order;
            $channel = $data['pay_channel'] ?? 'qris';
            if ($channel === 'kartu') {
                try {
                    $snap = $svc->snapToken($order->fresh(), \App\Services\MidtransService::enabledFor('kartu'));
                    $snaps[] = ['order_id' => $order->id, 'snap_token' => $snap['token']];
                } catch (\RuntimeException $e) {
                    $midtransError = $e->getMessage();
                }
            } else {
                try {
                    $directs[] = ['order_id' => $order->id, 'pay' => $svc->chargeDirect($order->fresh(), $channel, [
                        'bank' => $data['bank'] ?? null,
                        'ewallet' => $data['ewallet'] ?? null,
                        'store' => $data['store'] ?? null,
                    ])];
                } catch (\RuntimeException $e) {
                    $midtransError = $e->getMessage();
                }
            }
        }

        Cart::where('user_id', $request->user()->id)->whereIn('id', $items->pluck('id')->all())->delete();

        kirim_notifikasi($request->user()->id, 'Checkout berhasil (' . count($orders) . ' pesanan)',
            'Pesananmu sedang diproses admin. Pantau statusnya di dashboard.',
            'pesanan', 'pesanan', $orders[0]->id ?? null);

        return response()->json([
            'message' => 'Checkout berhasil (' . count($orders) . ' pesanan).',
            'orders' => $orders,
            'snaps' => $snaps,
            'directs' => $directs,
            'midtrans_error' => $midtransError,
            'client_key' => config('services.midtrans.client_key'),
            'is_production' => (bool) config('services.midtrans.is_production'),
        ], 201);
    }
}
