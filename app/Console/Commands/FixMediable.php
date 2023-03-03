<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use App\Models\Post;
use Illuminate\Console\Command;

class FixMediable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:mediable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix mediable data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->fixDataPageTranslations();
        $this->fixDataPosts();

        return Command::SUCCESS;
    }

    private function fixDataPageTranslations(): void
    {
        $pageTranslations = PageTranslation::all();

        foreach ($pageTranslations as $pageTranslation) {
            $mediaIds = collect($pageTranslation['data']['media'])
                ->pluck('id')
                ->all();

            if (!empty($mediaIds)) {
                $pageTranslation->syncMedia($mediaIds);
            }
        }
    }

    private function fixDataPosts(): void
    {
        $posts = Post::whereNotNull('cover_image_id')->get();

        foreach ($posts as $post) {
            $post->syncMedia([
                (int)$post->cover_image_id
            ]);
        }
    }
}
