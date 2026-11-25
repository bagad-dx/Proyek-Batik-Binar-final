<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_product_fields_and_image_in_produk_table(): void
    {
        Storage::fake('public');
        Storage::fake('product_images');

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $oldImage = UploadedFile::fake()->image('old.png')->store('products', 'public');
        $product = Product::create([
            'nama' => 'Batik Lama',
            'slug' => 'batik-lama',
            'harga' => 100000,
            'stok' => 2,
            'gambar' => $oldImage,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'nama' => 'Batik Baru',
            'category_id' => '',
            'jenis_id' => '',
            'deskripsi' => 'Deskripsi diperbarui',
            'harga' => 150000,
            'stok' => 5,
            'motif' => 'Iwak Etong',
            'bahan' => 'Katun',
            'ukuran' => '250 cm',
            'teknik' => 'Tulis',
            'shopee_url' => '',
            'status' => 'aktif',
            'gambar' => UploadedFile::fake()->image('baru.png'),
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $product->refresh();

        $this->assertSame('Batik Baru', $product->nama);
        $this->assertSame('150000.00', $product->harga);
        $this->assertSame(5, $product->stok);
        $this->assertStringStartsWith('assets/storage/products/', $product->gambar);
        $this->assertSame(asset($product->gambar), $product->gambar_url);
        $this->assertDatabaseHas('produk', [
            'id' => $product->id,
            'nama' => 'Batik Baru',
            'harga' => 150000,
            'stok' => 5,
            'motif' => 'Iwak Etong',
            'gambar' => $product->gambar,
        ]);
        Storage::disk('product_images')->assertExists(substr($product->gambar, strlen('assets/storage/')));
        Storage::disk('public')->assertMissing($oldImage);
    }

    public function test_edit_form_uses_the_file_field_expected_by_the_update_action(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin', 'is_active' => true]);
        $product = Product::create([
            'nama' => 'Batik Contoh',
            'slug' => 'batik-contoh',
            'harga' => 100000,
            'stok' => 1,
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('type="file"', false)
            ->assertSee('name="gambar"', false);
    }

    public function test_oversized_image_is_rejected_without_changing_the_product(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $product = Product::create([
            'nama' => 'Batik Lama',
            'slug' => 'batik-lama-besar',
            'harga' => 100000,
            'stok' => 2,
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'nama' => 'Batik Baru',
                'harga' => 150000,
                'stok' => 5,
                'status' => 'aktif',
                'gambar' => UploadedFile::fake()->image('terlalu-besar.jpg')->size(2049),
            ])
            ->assertSessionHasErrors([
                'gambar' => 'Ukuran gambar maksimal 2 MB.',
            ]);

        $this->assertDatabaseHas('produk', [
            'id' => $product->id,
            'nama' => 'Batik Lama',
            'harga' => 100000,
            'stok' => 2,
        ]);
    }
}
