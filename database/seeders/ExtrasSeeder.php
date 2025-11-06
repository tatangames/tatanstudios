<?php

namespace Database\Seeders;

use App\Models\Extras;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExtrasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Extras::create([
            'tema' => '0',
        ]);
    }
}
