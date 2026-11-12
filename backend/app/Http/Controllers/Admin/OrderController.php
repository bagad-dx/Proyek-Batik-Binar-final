<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $q = Order::with(['user', 'product'])->latest();
        if ($request->filled('status')) $q->where('status', $request->status);
        $orders = $q->paginate(10)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate(['status' => 'required|in:pending,diproses,dikirim,selesai,batal']);
        $wasBatal = $order->status === 'batal';
        $old = $order->status;
        $order->update($data);
        // stok kembali jika pesanan dibatalkan (dan sebelumnya belum batal)
        if ($data['status'] === 'batal' && ! $wasBatal && $order->product) {
            $order->product->increment('stok', $order->jumlah);
        }
        if ($old !== $data['status']) {
            $label = ['pending' => 'menunggu', 'diproses' => 'diproses', 'dikirim' => 'dikirim', 'selesai' => 'selesai', 'batal' => 'dibatalkan'][$data['status']] ?? $data['status'];
            kirim_notifikasi($order->user_id, 'Pesanan #' . $order->id . ' ' . $label,
                ($order->product->nama ?? 'Produk') . ' x' . $order->jumlah . ' — status terbaru: ' . $data['status'] . '.',
                'pesanan', 'pesanan', $order->id);
        }
        return back()->with('success', 'Status pesanan diupdate ke ' . $data['status']);
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return back()->with('success', 'Pesanan dihapus.');
    }
}
