<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatSetting;
use Illuminate\Http\Request;

class ShopSettingController extends Controller
{
    public function index()
    {
        $shopeeStore = ChatSetting::get('shopee_store_url');
        return view('admin.settings.index', compact('shopeeStore'));
    }

    public function update(Request $request)
    {
        $data = $request->validate(['shopee_store_url' => 'nullable|url|max:500']);
        ChatSetting::set('shopee_store_url', $data['shopee_store_url'] ?? '');
        return back()->with('success', 'Link toko Shopee disimpan.');
    }
}
