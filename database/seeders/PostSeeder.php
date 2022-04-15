<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\{
    Post,
    User
};
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
        $posts = [
            [
                'title' => 'Draft News',
                'slug' => Str::slug('Draft News'),
                'status' => Post::STATUS_DRAFT
            ],
            [
                'title' => 'Good News',
                'slug' => Str::slug('Good News'),
                'status' => Post::STATUS_PUBLISHED
            ],
            [
                'title' => 'Scheduled News',
                'slug' => Str::slug('Scheduled News'),
                'status' => Post::STATUS_SCHEDULED,
                'scheduled_at' => Carbon::now()->addDays(2)
            ],
        ];

        Post::factory()
            ->count(3)
            ->for($adminUser, 'author')
            ->state(new Sequence(
                $posts[0],
                $posts[1],
                $posts[2],
            ))
            ->hasAttached($category)
            ->fakeContent()
            ->create();
    }
}
