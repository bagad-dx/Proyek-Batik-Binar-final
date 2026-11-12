<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CatalogApiController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Publik (untuk fetch dari script.js / frontend statis)
Route::post('/register', [AuthApiController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:10,1');
// OTP pendaftaran (kode ke Email/Gmail atau WhatsApp)
Route::get('/otp/config', [OtpController::class, 'config']);
Route::post('/otp/request', [OtpController::class, 'request'])->middleware('throttle:5,1');
Route::post('/otp/resend', [OtpController::class, 'resend'])->middleware('throttle:5,1');
Route::post('/otp/verify', [OtpController::class, 'verify'])->middleware('throttle:10,1');
Route::get('/products', [CatalogApiController::class, 'products']);
Route::get('/products/{id}', [CatalogApiController::class, 'productDetail']);
Route::get('/galleries', [CatalogApiController::class, 'galleries']);
Route::get('/motifs', [CatalogApiController::class, 'motifs']);
Route::get('/jenis', [CatalogApiController::class, 'jenis']); // tb_jenis_produk Raka
Route::get('/tags', [CatalogApiController::class, 'tags']); // tb_tag_galeri Raka
Route::post('/chat', [ChatbotController::class, 'chat']); // chatbot Binar (publik)
Route::get('/chat/config', [ChatbotController::class, 'config']);
Route::post('/auth/google', [\App\Http\Controllers\GoogleAuthController::class, 'loginApi'])->middleware('throttle:10,1'); // login Google via API
// Meta publik untuk halaman native (ekspedisi, pembayaran, shopee, firebase)
Route::get('/meta', fn () => response()->json([
    'expeditions' => \App\Models\Order::EXPEDITIONS,
    'payments' => \App\Models\Order::PAYMENTS,
    'shopee_store_url' => \App\Models\ChatSetting::get('shopee_store_url'),
    'midtrans_client_key' => config('services.midtrans.client_key'),
    'midtrans_is_production' => (bool) config('services.midtrans.is_production'),
]));
Route::get('/firebase-config', fn () => response()->json(config('services.firebase')));
Route::post('/midtrans/callback', [CatalogApiController::class, 'midtransCallback']); // webhook (signature)
// Jembatan sesi web -> token native (cookie sesi, bukan token): untuk
// menyelaraskan token basi di localStorage halaman native se-origin.
Route::middleware(['web', 'throttle:30,1'])->get('/session-token', [AuthController::class, 'sessionToken']);

// Butuh token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthApiController::class, 'me']);
    Route::put('/profile', [AuthApiController::class, 'updateProfile']);
    Route::post('/profile/avatar', [AuthApiController::class, 'updateAvatar']);
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/dashboard/summary', [\App\Http\Controllers\Api\DashboardController::class, 'summary']);
    // Notifikasi
    Route::get('/notifications', [\App\Http\Controllers\Api\NotifikasiController::class, 'index']);
    Route::patch('/notifications/read', [\App\Http\Controllers\Api\NotifikasiController::class, 'read']);
    // Chat penjual
    Route::get('/chats', [\App\Http\Controllers\Api\ChatController::class, 'index']);
    Route::post('/chats', [\App\Http\Controllers\Api\ChatController::class, 'store']);
    Route::get('/chats/{chat}/messages', [\App\Http\Controllers\Api\ChatController::class, 'messages']);
    Route::post('/chats/{chat}/messages', [\App\Http\Controllers\Api\ChatController::class, 'send']);
    Route::get('/my-orders', [CatalogApiController::class, 'myOrders']);
    Route::post('/orders', [CatalogApiController::class, 'storeOrder']);
    Route::get('/orders/{order}/payment', [CatalogApiController::class, 'paymentData']);
    Route::patch('/orders/{order}/cancel', [CatalogApiController::class, 'cancelOrder']);
    // Wishlist
    Route::get('/wishlist', [\App\Http\Controllers\Api\WishlistController::class, 'index']);
    Route::post('/wishlist', [\App\Http\Controllers\Api\WishlistController::class, 'store']);
    Route::delete('/wishlist/{product}', [\App\Http\Controllers\Api\WishlistController::class, 'destroy']);
    // Keranjang
    Route::get('/cart', [\App\Http\Controllers\Api\CartController::class, 'index']);
    Route::post('/cart', [\App\Http\Controllers\Api\CartController::class, 'store']);
    Route::put('/cart/{cart}', [\App\Http\Controllers\Api\CartController::class, 'update']);
    Route::delete('/cart/{cart}', [\App\Http\Controllers\Api\CartController::class, 'destroy']);
    Route::delete('/cart', [\App\Http\Controllers\Api\CartController::class, 'clear']);
    Route::post('/cart/checkout', [\App\Http\Controllers\Api\CartController::class, 'checkout']);

    // Admin via API
    Route::middleware('role:admin,superadmin')->group(function () {
        Route::get('/admin/check', fn () => response()->json(['message' => 'Anda admin.']));
    });
    // Superadmin via API
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/superadmin/check', fn () => response()->json(['message' => 'Anda superadmin.']));
    });
});
