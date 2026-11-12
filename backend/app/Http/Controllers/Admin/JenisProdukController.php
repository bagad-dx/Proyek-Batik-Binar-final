<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// Kelola jenis produk (tb_jenis_produk Raka): pil filter halaman belanja.
class JenisProdukController extends Controller
{
    public function index()
    {
        $jenis = JenisProduk::withCount('products')->latest()->paginate(10);
        return view('admin.jenis.index', compact('jenis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'label_display' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);
        $data['slug'] = Str::slug($data['nama']) . '-' . Str::random(4);
        $data['label_display'] = $data['label_display'] ?: $data['nama'];
        JenisProduk::create($data);
        return back()->with('success', 'Jenis produk ditambahkan.');
    }

    public function update(Request $request, JenisProduk $jeni)
    {
        $data = $request->validate([
            'label_display' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);
        $jeni->update($data);
        return back()->with('success', 'Jenis produk diupdate.');
    }

    public function destroy(JenisProduk $jeni)
    {
        $jeni->delete();
        return back()->with('success', 'Jenis produk dihapus.');
    }
}
