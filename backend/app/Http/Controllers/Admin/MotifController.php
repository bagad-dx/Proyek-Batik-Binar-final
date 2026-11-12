<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Motif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MotifController extends Controller
{
    public function index()
    {
        $motifs = Motif::latest()->paginate(10);
        return view('admin.motifs.index', compact('motifs'));
    }

    public function create() { return view('admin.motifs.form', ['motif' => new Motif()]); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'asal' => 'nullable|string|max:255',
            'filosofi' => 'nullable|string',
            'lama_pengerjaan' => 'nullable|string|max:100',
            'teknik' => 'nullable|string|max:255',
            'gambar' => 'nullable|image|max:3072',
        ]);
        $data['user_id'] = Auth::id();
        $data['is_published'] = $request->boolean('is_published', true);
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('motifs', 'public');
        }
        Motif::create($data);
        return redirect()->route('admin.motifs.index')->with('success', 'Motif ditambahkan.');
    }

    public function edit(Motif $motif) { return view('admin.motifs.form', compact('motif')); }

    public function update(Request $request, Motif $motif)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'asal' => 'nullable|string|max:255',
            'filosofi' => 'nullable|string',
            'lama_pengerjaan' => 'nullable|string|max:100',
            'teknik' => 'nullable|string|max:255',
            'gambar' => 'nullable|image|max:3072',
        ]);
        $data['is_published'] = $request->boolean('is_published', true);
        if ($request->hasFile('gambar')) {
            if ($motif->gambar && ! str_starts_with($motif->gambar, 'http')) {
                Storage::disk('public')->delete($motif->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('motifs', 'public');
        }
        $motif->update($data);
        return redirect()->route('admin.motifs.index')->with('success', 'Motif diupdate.');
    }

    public function destroy(Motif $motif)
    {
        if ($motif->gambar && ! str_starts_with($motif->gambar, 'http')) {
            Storage::disk('public')->delete($motif->gambar);
        }
        $motif->delete();
        return redirect()->route('admin.motifs.index')->with('success', 'Motif dihapus.');
    }
}
