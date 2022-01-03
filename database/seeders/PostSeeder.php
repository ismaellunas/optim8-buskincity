<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminUser = User::find(1);
        $category = Category::find(1);
        Post::factory()
            ->count(20)
            ->for($adminUser, 'author')
            ->state(new Sequence(
                ['status' => Post::STATUS_DRAFT],
                ['status' => Post::STATUS_PUBLISHED],
            ))
            ->hasAttached($category)
            ->fakeContent()
            ->create();
    }
}
