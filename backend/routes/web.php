<?php

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\MotifController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Superadmin\UserController;
use App\Models\Gallery;
use App\Models\Motif;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

// Homepage NATIVE (file statis, sama persis folder FrontEnd/PROYEK)
Route::get('/', fn () => response()->file(public_path('index.html')))->name('home');

// Koleksi motif lengkap (publik)
Route::get('/koleksi', function (\Illuminate\Http\Request $request) {
    $q = Motif::where('is_published', true)->latest();
    if ($request->filled('search')) $q->where('nama', 'like', '%' . $request->search . '%');
    $motifs = $q->paginate(12)->withQueryString();
    return view('koleksi.index', compact('motifs'));
})->name('koleksi.index');

// Belanja wajib login (tamu diarahkan ke halaman login)
Route::middleware(['auth', 'role:user,admin,superadmin'])->group(function () {
    Route::get('/belanja', [OrderController::class, 'shop'])->name('shop.index');
    Route::get('/belanja/{product}', [OrderController::class, 'show'])->name('shop.show');
});

// Auth web SATU PINTU: user, admin, superadmin login di /login yang sama.
// Role dibaca dari akun terdaftar lalu diarahkan otomatis ke dashboard/panel.
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    // OTP pendaftaran web: verifikasi kode dari Email/WA sebelum akun dibuat.
    Route::get('/register/otp', [AuthController::class, 'showOtpForm'])->name('register.otp');
    Route::post('/register/otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('register.otp.verify');
    Route::post('/register/otp/resend', [AuthController::class, 'resendOtp'])->middleware('throttle:5,1')->name('register.otp.resend');
    Route::post('/auth/google', [\App\Http\Controllers\GoogleAuthController::class, 'loginWeb'])->name('auth.google');
});

// ALIAS: /panel/login diteruskan ke /login utama (satu pintu semua role).
Route::middleware('guest')->prefix('panel')->name('panel.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Panel\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Panel\AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// USER
Route::middleware(['auth', 'role:user,admin,superadmin'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'user'])->name('dashboard');
    Route::put('/profile', [AuthController::class, 'updateProfileWeb'])->name('profile.update');
    Route::post('/profile/avatar', [AuthController::class, 'updateAvatarWeb'])->name('profile.avatar');
    Route::get('/katalog', [OrderController::class, 'catalog'])->name('catalog');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
    Route::get('/orders/{order}/payment-data', [OrderController::class, 'paymentData'])->name('orders.paymentData');
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/checkout/{product}', [OrderController::class, 'create'])->name('checkout');
    Route::post('/checkout/{product}', [OrderController::class, 'store'])->name('checkout.store');
});

// PANEL ADMIN (di bawah /panel) — kelola postingan jual beli, galeri, motif, kategori, pesanan
Route::middleware(['auth', 'role:admin,superadmin'])->prefix('panel/admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('galleries', GalleryController::class)->except(['show']);
    Route::resource('motifs', MotifController::class)->except(['show']);
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('jenis', [\App\Http\Controllers\Admin\JenisProdukController::class, 'index'])->name('jenis.index');
    Route::post('jenis', [\App\Http\Controllers\Admin\JenisProdukController::class, 'store'])->name('jenis.store');
    Route::put('jenis/{jeni}', [\App\Http\Controllers\Admin\JenisProdukController::class, 'update'])->name('jenis.update');
    Route::delete('jenis/{jeni}', [\App\Http\Controllers\Admin\JenisProdukController::class, 'destroy'])->name('jenis.destroy');
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::put('orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
    Route::delete('orders/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');
    Route::get('settings', [\App\Http\Controllers\Admin\ShopSettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [\App\Http\Controllers\Admin\ShopSettingController::class, 'update'])->name('settings.update');
    Route::get('otp', [\App\Http\Controllers\Admin\OtpSettingController::class, 'index'])->name('otp.index');
    Route::put('otp', [\App\Http\Controllers\Admin\OtpSettingController::class, 'update'])->name('otp.update');
    Route::post('otp/test', [\App\Http\Controllers\Admin\OtpSettingController::class, 'test'])->name('otp.test');
    Route::post('otp/local-mode', [\App\Http\Controllers\Admin\OtpSettingController::class, 'toggleLocalMode'])->name('otp.local');
    Route::get('chats', [\App\Http\Controllers\Admin\ChatController::class, 'index'])->name('chats.index');
    Route::get('chats/{chat}', [\App\Http\Controllers\Admin\ChatController::class, 'show'])->name('chats.show');
    Route::post('chats/{chat}/reply', [\App\Http\Controllers\Admin\ChatController::class, 'reply'])->name('chats.reply');
    Route::get('contents', [\App\Http\Controllers\Admin\ContentController::class, 'index'])->name('contents.index');
    Route::put('contents', [\App\Http\Controllers\Admin\ContentController::class, 'update'])->name('contents.update');
});

// PANEL SUPERADMIN (di bawah /panel) — kelola user & admin (+ semua hak admin)
Route::middleware(['auth', 'role:superadmin'])->prefix('panel/superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'superadmin'])->name('dashboard');
    Route::resource('users', UserController::class)->except(['show']);
    Route::patch('users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
    Route::get('users/{user}/ban', [UserController::class, 'showBan'])->name('users.ban');
    Route::put('users/{user}/ban', [UserController::class, 'ban'])->name('users.banStore');
    Route::patch('users/{user}/unban', [UserController::class, 'unban'])->name('users.unban');
    // Kelola chatbot
    Route::get('/chatbot', [\App\Http\Controllers\Superadmin\ChatbotController::class, 'index'])->name('chatbot.index');
    Route::put('/chatbot/settings', [\App\Http\Controllers\Superadmin\ChatbotController::class, 'updateSettings'])->name('chatbot.settings');
    Route::get('/chatbot/create', [\App\Http\Controllers\Superadmin\ChatbotController::class, 'create'])->name('chatbot.create');
    Route::post('/chatbot', [\App\Http\Controllers\Superadmin\ChatbotController::class, 'store'])->name('chatbot.store');
    Route::get('/chatbot/{chatbot}/edit', [\App\Http\Controllers\Superadmin\ChatbotController::class, 'edit'])->name('chatbot.edit');
    Route::put('/chatbot/{chatbot}', [\App\Http\Controllers\Superadmin\ChatbotController::class, 'update'])->name('chatbot.update');
    Route::delete('/chatbot/{chatbot}', [\App\Http\Controllers\Superadmin\ChatbotController::class, 'destroy'])->name('chatbot.destroy');
    Route::patch('/chatbot/{chatbot}/toggle', [\App\Http\Controllers\Superadmin\ChatbotController::class, 'toggle'])->name('chatbot.toggle');
    Route::post('/chatbot/test-ai', [\App\Http\Controllers\Superadmin\ChatbotController::class, 'testAi'])->name('chatbot.test');
});
