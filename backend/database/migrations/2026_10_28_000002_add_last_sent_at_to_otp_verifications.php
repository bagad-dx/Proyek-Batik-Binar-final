<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Waktu terakhir kode benar-benar dikirim. Dipakai untuk
     * cooldown kirim ulang di sisi server (bukan hanya tombol frontend).
     */
    public function up(): void
    {
        Schema::table('otp_verifications', function (Blueprint $table) {
            $table->timestamp('last_sent_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('otp_verifications', function (Blueprint $table) {
            $table->dropColumn('last_sent_at');
        });
    }
};
