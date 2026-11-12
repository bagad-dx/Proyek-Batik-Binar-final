<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('pertanyaan'); // judul untuk admin
            $table->text('keywords'); // kata kunci dipisah koma, cth: best seller,terlaris
            $table->text('jawaban'); // boleh HTML sederhana (b, br, i)
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_faqs');
        Schema::dropIfExists('chat_settings');
    }
};
