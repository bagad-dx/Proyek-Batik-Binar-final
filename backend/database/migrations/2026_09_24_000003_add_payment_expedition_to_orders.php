<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->default('transfer')->after('no_hp');
            $table->string('expedition')->default('jne')->after('payment_method');
            $table->decimal('shipping_cost', 15, 2)->default(0)->after('expedition');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'expedition', 'shipping_cost']);
        });
    }
};
