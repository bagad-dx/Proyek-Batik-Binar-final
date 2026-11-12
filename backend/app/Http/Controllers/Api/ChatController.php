<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Percakapan;
use App\Models\PesanChat;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /** Daftar percakapan pembeli + unread. */
    public function index(Request $request)
    {
        $chats = Percakapan::with(['pesanTerakhir', 'order.product'])
            ->where('user_id', $request->user()->id)->latest()->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'order_id' => $c->order_id,
                'produk' => $c->order?->product?->nama,
                'terakhir' => $c->pesanTerakhir?->pesan,
                'waktu' => $c->pesanTerakhir?->created_at,
                'belum_dibaca' => $c->belumDibacaOleh($request->user()->id),
            ]);
        return response()->json($chats);
    }

    /** Buat/ambil percakapan untuk sebuah pesanan (hanya pemilik). */
    public function store(Request $request)
    {
        $data = $request->validate(['order_id' => 'required|exists:pesanan,id']);
        $order = Order::findOrFail($data['order_id']);
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        $chat = Percakapan::firstOrCreate(
            ['user_id' => $request->user()->id, 'order_id' => $order->id]
        );
        return response()->json($chat, 201);
    }

    /** Isi thread + tandai pesan admin dibaca. */
    public function messages(Request $request, Percakapan $chat)
    {
        abort_unless((int) $chat->user_id === (int) $request->user()->id, 403);
        $chat->pesan()->where('pengirim_id', '!=', $request->user()->id)->whereNull('dibaca_at')->update(['dibaca_at' => now()]);
        return response()->json(
            $chat->pesan()->with('pengirim:id,name,role')->oldest()->take(100)->get()
        );
    }

    /** Kirim pesan ke penjual + notifikasi admin. */
    public function send(Request $request, Percakapan $chat)
    {
        abort_unless((int) $chat->user_id === (int) $request->user()->id, 403);
        $data = $request->validate(['pesan' => 'required|string|max:1000']);
        $msg = $chat->pesan()->create(['pengirim_id' => $request->user()->id, 'pesan' => $data['pesan']]);
        $admins = User::whereIn('role', ['admin', 'superadmin'])->where('is_active', true)->pluck('id');
        foreach ($admins as $aid) {
            kirim_notifikasi($aid, 'Chat baru dari ' . $request->user()->name,
                mb_substr($data['pesan'], 0, 120), 'chat', 'chat', $chat->id);
        }
        return response()->json($msg->load('pengirim:id,name,role'), 201);
    }
}
