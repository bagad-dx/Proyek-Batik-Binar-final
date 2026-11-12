<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Percakapan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $chats = Percakapan::with(['user', 'order.product', 'pesanTerakhir'])
            ->latest()->paginate(10);
        return view('admin.chats.index', compact('chats'));
    }

    public function show(Percakapan $chat)
    {
        $chat->load(['user', 'order.product', 'pesan.pengirim']);
        $chat->pesan()->where('pengirim_id', '!=', Auth::id())->whereNull('dibaca_at')->update(['dibaca_at' => now()]);
        $messages = $chat->pesan()->with('pengirim')->oldest()->take(100)->get();
        return view('admin.chats.show', compact('chat', 'messages'));
    }

    public function reply(Request $request, Percakapan $chat)
    {
        $data = $request->validate(['pesan' => 'required|string|max:1000']);
        $chat->pesan()->create(['pengirim_id' => Auth::id(), 'pesan' => $data['pesan']]);
        kirim_notifikasi($chat->user_id, 'Balasan penjual Batik Binar',
            mb_substr($data['pesan'], 0, 150), 'chat', 'chat', $chat->id);
        return back()->with('success', 'Balasan terkirim.');
    }
}
