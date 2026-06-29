<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        Category::create([
            'name' => 'Bahan Kimia Cair',
            'is_active' => true,
            'attributes' => ['rack' => 'Zone-A1', 'temperature' => 'Cool']
        ]);

        Category::create([
            'name' => 'Logam & Besi Baja',
            'is_active' => true,
            'attributes' => ['rack' => 'Zone-B3', 'temperature' => 'Normal']
        ]);
    }
}