<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $name = 'News';

        Category::factory()
            ->hasTranslations(
                1,
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                ]
            )
            ->create();
    }
}
