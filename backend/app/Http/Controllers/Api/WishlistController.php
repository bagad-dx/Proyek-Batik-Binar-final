<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Wishlist::with('product.category')->where('user_id', $request->user()->id)->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate(['product_id' => 'required|exists:produk,id']);
        $item = Wishlist::firstOrCreate(['user_id' => $request->user()->id, 'product_id' => $data['product_id']]);
        return response()->json(['message' => 'Ditambah ke wishlist.', 'item' => $item->load('product')], 201);
    }

    public function destroy(Request $request, Product $product)
    {
        Wishlist::where('user_id', $request->user()->id)->where('product_id', $product->id)->delete();
        return response()->json(['message' => 'Dihapus dari wishlist.']);
    }
}
