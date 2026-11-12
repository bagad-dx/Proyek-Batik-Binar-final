<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Muat helper global (cadangan bila composer dump-autoload belum jalan)
        $helpers = app_path('helpers.php');
        if (is_file($helpers)) require_once $helpers;
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.batik');

        // Terapkan kredensial OTP yang disimpan dari panel admin
        // (menimpa MAIL_* / FONNTE_TOKEN di .env bila sudah diisi).
        try {
            \App\Models\OtpSetting::applyToConfig();
        } catch (\Throwable $e) {
            // Tabel belum dimigrasi / DB mati: biarkan .env yang dipakai.
        }

        // Badge bel notifikasi di navbar website (layouts.app) untuk user login.
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $count = 0;
            try {
                if (auth()->check()) {
                    $count = \App\Models\Notifikasi::where('user_id', auth()->id())->whereNull('dibaca_at')->count();
                }
            } catch (\Throwable $e) {}
            $view->with('unreadNotif', $count);
        });
    }
}
