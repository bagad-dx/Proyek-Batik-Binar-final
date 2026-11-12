<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengaturan pengiriman OTP yang bisa diisi dari panel admin
     * (tanpa mengedit .env manual). Nilainya menimpa config mail /
     * Fonnte saat aplikasi boot.
     */
    public function up(): void
    {
        Schema::create('pengaturan_otp', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_otp');
    }
};
