<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\JenisProduk;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = Product::with('category')->latest();
        if ($request->filled('search')) {
            $q->where('nama', 'like', '%' . $request->search . '%');
        }
        $products = $q->paginate(10)->withQueryString();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $jenis = JenisProduk::all();
        return view('admin.products.form', ['product' => new Product(), 'categories' => $categories, 'jenis' => $jenis]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'category_id' => 'nullable|exists:kategori,id',
            'jenis_id' => 'nullable|exists:jenis_produk,id',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'motif' => 'nullable|string|max:255',
            'bahan' => 'nullable|string|max:255',
            'ukuran' => 'nullable|string|max:255',
            'teknik' => 'nullable|string|max:255',
            'shopee_url' => 'nullable|url|max:500',
            'status' => 'required|in:aktif,nonaktif,draft',
            'gambar' => 'nullable|image|max:2048',
        ], [
            'gambar.image' => 'File gambar harus berupa gambar yang valid.',
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);

        $data['slug'] = Str::slug($data['nama']) . '-' . Str::random(5);
        $data['user_id'] = Auth::id();
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $this->storeProductImage($request);
        }

        Product::create($data);
        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $jenis = JenisProduk::all();
        return view('admin.products.form', compact('product', 'categories', 'jenis'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'category_id' => 'nullable|exists:kategori,id',
            'jenis_id' => 'nullable|exists:jenis_produk,id',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'motif' => 'nullable|string|max:255',
            'bahan' => 'nullable|string|max:255',
            'ukuran' => 'nullable|string|max:255',
            'teknik' => 'nullable|string|max:255',
            'shopee_url' => 'nullable|url|max:500',
            'status' => 'required|in:aktif,nonaktif,draft',
            'gambar' => 'nullable|image|max:2048',
        ], [
            'gambar.image' => 'File gambar harus berupa gambar yang valid.',
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);

        $previousImage = $product->gambar;
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $this->storeProductImage($request);
        }

        $product->update($data);

        if ($request->hasFile('gambar')) {
            $this->deleteProductImage($previousImage);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diupdate.');
    }

    public function destroy(Product $product)
    {
        $this->deleteProductImage($product->gambar);
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Produk dihapus.');
    }

    private function storeProductImage(Request $request): string
    {
        $path = $request->file('gambar')->store('products', 'product_images');

        if (! is_string($path) || $path === '') {
            throw new \RuntimeException('Gagal menyimpan gambar produk.');
        }

        return 'assets/storage/' . $path;
    }

    private function deleteProductImage(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http')) {
            return;
        }

        if (str_starts_with($path, 'assets/storage/')) {
            Storage::disk('product_images')->delete(substr($path, strlen('assets/storage/')));
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
