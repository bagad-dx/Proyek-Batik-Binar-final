<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Gabungan fitur native Raka (tb_jenis_produk, tb_tag_galeri, tb_etalase,
// tb_galeri, tb_detail_pemesanan) ke skema PROYEK (batik_binar).
// Idempotent: semua pembuatan dijaga hasTable/hasColumn agar aman
// dijalankan di database yang sudah di-merge via merge_raka.sql.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jenis_produk')) {
            Schema::create('jenis_produk', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('nama');
                $table->string('label_display');
                $table->text('deskripsi')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tag_galeri')) {
            Schema::create('tag_galeri', function (Blueprint $table) {
                $table->id();
                $table->string('label')->unique();
                $table->timestamps();
            });
        }

        Schema::table('produk', function (Blueprint $table) {
            if (! Schema::hasColumn('produk', 'ukuran')) {
                $table->string('ukuran')->nullable()->after('bahan');
            }
            if (! Schema::hasColumn('produk', 'teknik')) {
                $table->string('teknik')->nullable()->after('ukuran');
            }
            if (! Schema::hasColumn('produk', 'jenis_id')) {
                $table->unsignedBigInteger('jenis_id')->nullable()->after('category_id');
                $table->index('jenis_id');
            }
        });

        Schema::table('galeri', function (Blueprint $table) {
            if (! Schema::hasColumn('galeri', 'filosofi')) {
                $table->text('filosofi')->nullable()->after('deskripsi');
            }
            if (! Schema::hasColumn('galeri', 'tahun_pembuatan')) {
                $table->date('tahun_pembuatan')->nullable()->after('filosofi');
            }
            if (! Schema::hasColumn('galeri', 'motif_id')) {
                $table->unsignedBigInteger('motif_id')->nullable()->after('tahun_pembuatan');
                $table->index('motif_id');
            }
            if (! Schema::hasColumn('galeri', 'tag')) {
                $table->string('tag', 100)->nullable()->after('motif_id');
            }
        });

        if (! Schema::hasTable('detail_pesanan')) {
            Schema::create('detail_pesanan', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pesanan_id');
                $table->unsignedBigInteger('product_id');
                $table->integer('jumlah')->default(1);
                $table->decimal('harga_satuan', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->timestamps();
                $table->index('pesanan_id');
                $table->index('product_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
        Schema::table('galeri', function (Blueprint $table) {
            $table->dropColumn(['filosofi', 'tahun_pembuatan', 'motif_id', 'tag']);
        });
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn(['ukuran', 'teknik', 'jenis_id']);
        });
        Schema::dropIfExists('tag_galeri');
        Schema::dropIfExists('jenis_produk');
    }
};
