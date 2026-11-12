<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['nama' => 'required|string|max:255', 'deskripsi' => 'nullable|string']);
        $data['slug'] = Str::slug($data['nama']) . '-' . Str::random(4);
        Category::create($data);
        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate(['nama' => 'required|string|max:255', 'deskripsi' => 'nullable|string']);
        $category->update($data);
        return back()->with('success', 'Kategori diupdate.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }
}
