<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // admin pembuat
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 15, 2)->default(0);
            $table->integer('stok')->default(0);
            $table->string('gambar')->nullable(); // path storage
            $table->string('motif')->nullable(); // Iwak Etong, Kapal Kandas, dll
            $table->string('bahan')->nullable(); // sutra, katun primissima
            $table->enum('status', ['aktif', 'nonaktif', 'draft'])->default('aktif');
            $table->timestamps();
        });

        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('judul');
            $table->string('kategori')->default('umum'); // Editorial Busana, Artisan Craft, Detail Tekstur
            $table->text('deskripsi')->nullable();
            $table->string('gambar')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('motifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama'); // Iwak Etong
            $table->string('asal')->default('Paoman, Indramayu');
            $table->text('filosofi')->nullable();
            $table->string('lama_pengerjaan')->nullable(); // 40 Hari
            $table->string('teknik')->default('Tulis Complongan');
            $table->string('gambar')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // pembeli
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('jumlah')->default(1);
            $table->decimal('total_harga', 15, 2);
            $table->text('alamat')->nullable();
            $table->string('no_hp')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['pending', 'diproses', 'dikirim', 'selesai', 'batal'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('motifs');
        Schema::dropIfExists('galleries');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
