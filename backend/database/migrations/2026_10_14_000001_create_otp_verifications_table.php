<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * OTP pendaftaran akun (kirim ke Email/Gmail atau WhatsApp).
     * Satu baris = satu kode aktif per email. Data calon akun disimpan
     * di sini (termasuk password yang SUDAH di-hash) sampai kode
     * diverifikasi, baru baris pengguna dibuat di tabel `pengguna`.
     */
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username', 30)->nullable();
            $table->string('email')->index();
            $table->string('phone', 20);
            $table->string('password'); // hash bcrypt calon password
            $table->string('channel', 10)->default('email'); // email | wa
            $table->string('code_hash'); // hash bcrypt dari 6 digit OTP
            $table->unsignedTinyInteger('attempts')->default(0); // salah input ke-
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->unique('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
