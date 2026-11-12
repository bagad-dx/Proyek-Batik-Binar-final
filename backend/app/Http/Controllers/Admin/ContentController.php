<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function index()
    {
        $groups = SiteContent::orderBy('section')->orderBy('order')->get()->groupBy('section');
        $labels = ['hero' => 'Hero (Beranda atas)', 'tentang' => 'Tentang Kami', 'proses' => 'Proses & Tahapan', 'motif' => 'Judul Koleksi', 'galeri' => 'Judul Galeri', 'cta' => 'Banner Ajakan (CTA)', 'kontak' => 'Kontak & Footer'];
        return view('admin.contents.index', compact('groups', 'labels'));
    }

    public function update(Request $request)
    {
        $contents = SiteContent::all()->keyBy('key');

        foreach ($request->input('content', []) as $key => $value) {
            if (! isset($contents[$key])) continue;
            if ($contents[$key]->type === 'image') continue; // gambar via URL teks / upload di bawah
            $contents[$key]->update(['value' => $value]);
        }

        // URL gambar via kolom teks
        foreach ($request->input('image_url', []) as $key => $url) {
            if (! isset($contents[$key]) || $contents[$key]->type !== 'image') continue;
            if ($url !== null && $url !== '') $contents[$key]->update(['value' => trim($url)]);
        }

        // Upload gambar (menang atas URL)
        foreach ($request->file('image_file', []) as $key => $file) {
            if (! isset($contents[$key]) || $contents[$key]->type !== 'image' || ! $file) continue;
            if (! str_starts_with($file->getMimeType(), 'image/')) continue;
            $path = $file->store('konten', 'public');
            if ($contents[$key]->value && ! str_starts_with($contents[$key]->value, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($contents[$key]->value);
            }
            $contents[$key]->update(['value' => $path]);
        }

        SiteContent::flush();
        return back()->with('success', 'Konten website disimpan.');
    }
}
