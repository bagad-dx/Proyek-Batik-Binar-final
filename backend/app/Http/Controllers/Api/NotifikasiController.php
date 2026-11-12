<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $items = Notifikasi::where('user_id', $request->user()->id)->latest()->take(30)->get();
        return response()->json([
            'belum_dibaca' => Notifikasi::where('user_id', $request->user()->id)->whereNull('dibaca_at')->count(),
            'items' => $items,
        ]);
    }

    public function read(Request $request)
    {
        $q = Notifikasi::where('user_id', $request->user()->id)->whereNull('dibaca_at');
        if ($request->filled('id')) $q->where('id', $request->id);
        $q->update(['dibaca_at' => now()]);
        return response()->json(['message' => 'Ditandai dibaca.']);
    }
}
