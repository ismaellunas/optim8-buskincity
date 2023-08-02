<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class FixFormBuilderShortcodeInPostContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:form-builder-shortcode-in-post-content';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Fix form-builder shortcode in post content";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Post::chunk(10, function ($posts) {
            foreach ($posts as $post) {
                $content = preg_replace(
                    '/<p>\\s*?\[form-builder\s+form-id="(.*?)"\]?\\s*<\\/p>/',
                    '[form-builder form_id="$1"]',
                    $post->content
                );

                $content = preg_replace(
                    '/\[form-builder\s+form-id="(.*?)"\]/',
                    '[form-builder form_id="$1"]',
                    $content
                );

                $post->content = $content;
                $post->save();
            }
        });

        return Command::SUCCESS;
    }
}
