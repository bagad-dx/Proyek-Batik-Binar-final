<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(BatikSeeder::class);
        $this->call(ChatbotSeeder::class);
        $this->call(KoleksiDriveSeeder::class);
        $this->call(SiteContentSeeder::class);
        $this->call(NativeShopSeeder::class);
        $this->call(RakaMergeSeeder::class);
    }
}
