<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Motif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::latest()->paginate(12);
        return view('admin.galleries.index', compact('galleries'));
    }

    public function create()
    {
        $motifs = Motif::orderBy('nama')->get();
        return view('admin.galleries.form', ['gallery' => new Gallery(), 'motifs' => $motifs]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'filosofi' => 'nullable|string',
            'tahun_pembuatan' => 'nullable|date',
            'motif_id' => 'nullable|exists:motif,id',
            'tag' => 'nullable|string|max:100',
            'is_published' => 'boolean',
            'gambar' => 'nullable|image|max:3072',
        ]);
        $data['user_id'] = Auth::id();
        $data['is_published'] = $request->boolean('is_published', true);
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('galleries', 'public');
        }
        Gallery::create($data);
        return redirect()->route('admin.galleries.index')->with('success', 'Galeri ditambahkan.');
    }

    public function edit(Gallery $gallery)
    {
        $motifs = Motif::orderBy('nama')->get();
        return view('admin.galleries.form', compact('gallery', 'motifs'));
    }

    public function update(Request $request, Gallery $gallery)
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'filosofi' => 'nullable|string',
            'tahun_pembuatan' => 'nullable|date',
            'motif_id' => 'nullable|exists:motif,id',
            'tag' => 'nullable|string|max:100',
            'gambar' => 'nullable|image|max:3072',
        ]);
        $data['is_published'] = $request->boolean('is_published', true);
        if ($request->hasFile('gambar')) {
            if ($gallery->gambar && ! str_starts_with($gallery->gambar, 'http')) {
                Storage::disk('public')->delete($gallery->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('galleries', 'public');
        }
        $gallery->update($data);
        return redirect()->route('admin.galleries.index')->with('success', 'Galeri diupdate.');
    }

    public function destroy(Gallery $gallery)
    {
        if ($gallery->gambar && ! str_starts_with($gallery->gambar, 'http')) {
            Storage::disk('public')->delete($gallery->gambar);
        }
        $gallery->delete();
        return redirect()->route('admin.galleries.index')->with('success', 'Galeri dihapus.');
    }
}
