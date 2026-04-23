<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Streaming', 'icon' => 'bi-play-btn', 'color' => '#E50914'],
            ['name' => 'Produtividade', 'icon' => 'bi-briefcase', 'color' => '#0F6CBD'],
            ['name' => 'Cloud Storage', 'icon' => 'bi-cloud', 'color' => '#34A853'],
            ['name' => 'Games', 'icon' => 'bi-controller', 'color' => '#107C10'],
            ['name' => 'Música', 'icon' => 'bi-music-note-beamed', 'color' => '#1DB954'],
            ['name' => 'Educação', 'icon' => 'bi-mortarboard', 'color' => '#F4B400'],
            ['name' => 'Domínios', 'icon' => 'bi-globe', 'color' => '#4285F4'],
            ['name' => 'Financeiro', 'icon' => 'bi-bank', 'color' => '#0F9D58'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'is_system' => true,
                ]
            );
        }
    }
}
