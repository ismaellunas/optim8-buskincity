<?php

namespace Tests\Feature\Route;

use App\Models\{
    Category,
    Page,
    PageTranslation,
    Post,
};
use App\Services\TranslationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\LaravelLocalization;
use Tests\TestCase;

abstract class BaseRouteTestCase extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    // https://github.com/mcamara/laravel-localization#testing
    protected function refreshApplicationWithLocale($locale)
    {
        if (TranslationService::getDefaultLocale() != $locale) {
            self::tearDown();
            putenv(LaravelLocalization::ENV_ROUTE_KEY . '=' . $locale);
            self::setUp();
        }
    }

    protected function tearDown(): void
    {
        putenv(LaravelLocalization::ENV_ROUTE_KEY);
        parent::tearDown();
    }

    protected function getCategory(): Category
    {
        return Category::factory()
            ->hasTranslations(1, ['name' => 'News'])
            ->create();
    }

    protected function getPublishedPost(): Post
    {
        return Post::factory()
            ->hasAttached($this->getCategory())
            ->fakeContent()
            ->create(
                [
                    'title' => 'Published Post',
                    'slug' => Str::slug('Published Post'),
                    'status' => Post::STATUS_PUBLISHED
                ]
            );
    }

    protected function getScheduledPost(): Post
    {
        return Post::factory()
            ->hasAttached($this->getCategory())
            ->fakeContent()
            ->create(
                [
                    'title' => 'Scheduled Post',
                    'slug' => Str::slug('Scheduled Post'),
                    'status' => Post::STATUS_SCHEDULED,
                    'scheduled_at' => Carbon::now()->addDays(2)
                ]
            );
    }

    protected function getDraftPost(): Post
    {
        return Post::factory()
            ->hasAttached($this->getCategory())
            ->fakeContent()
            ->create(
                [
                    'title' => 'Draft Post',
                    'slug' => Str::slug('Draft Post'),
                    'status' => Post::STATUS_DRAFT
                ]
            );
    }

    protected function getPublishedPage(): Page
    {
        return Page::factory()
            ->hasTranslations(1, [
                'title' => 'Published Page',
                'slug' => Str::slug('Published Page'),
                'status' => PageTranslation::STATUS_PUBLISHED,
                'data' => [
                    "structures" => [],
                    "entities" => [],
                    "media" => []
                ],
            ])
            ->create();
    }

    protected function getDraftPage(): Page
    {
        return Page::factory()
            ->hasTranslations(1, [
                'title' => 'Draft Page',
                'slug' => Str::slug('Draft Page'),
                'status' => PageTranslation::STATUS_DRAFT,
                'data' => [
                    "structures" => [],
                    "entities" => [],
                    "media" => []
                ]
            ])
            ->create();
    }
}
