<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('pengguna')->cascadeOnDelete();
            $table->string('judul');
            $table->text('pesan');
            $table->string('tipe')->default('info'); // pesanan, pembayaran, chat, akun
            $table->string('referensi_tipe')->nullable(); // pesanan, chat
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();
        });

        Schema::create('percakapan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('pengguna')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('pesanan')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pesan_chat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('percakapan_id')->constrained('percakapan')->cascadeOnDelete();
            $table->foreignId('pengirim_id')->constrained('pengguna')->cascadeOnDelete();
            $table->text('pesan');
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesan_chat');
        Schema::dropIfExists('percakapan');
        Schema::dropIfExists('notifikasi');
    }
};
