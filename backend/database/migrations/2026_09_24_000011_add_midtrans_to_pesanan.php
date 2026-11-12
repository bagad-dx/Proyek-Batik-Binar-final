<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('payment_ref')->nullable()->after('payment_method');
            $table->string('midtrans_status')->nullable()->after('payment_ref');
            $table->string('snap_token', 500)->nullable()->after('midtrans_status');
            $table->timestamp('paid_at')->nullable()->after('snap_token');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn(['payment_ref', 'midtrans_status', 'snap_token', 'paid_at']);
        });
    }
};
