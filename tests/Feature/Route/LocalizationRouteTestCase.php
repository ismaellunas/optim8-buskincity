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
use Mcamara\LaravelLocalization\LaravelLocalization;
use Tests\TestCase;

abstract class LocalizationRouteTestCase extends TestCase
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

    protected function createCategory(): Category
    {
        return Category::factory()
            ->hasTranslations(1)
            ->create();
    }

    protected function createPublishedPost(): Post
    {
        return Post::factory()
            ->hasAttached($this->createCategory())
            ->fakeContent()
            ->create(
                [
                    'status' => Post::STATUS_PUBLISHED
                ]
            );
    }

    protected function createScheduledPost(): Post
    {
        return Post::factory()
            ->hasAttached($this->createCategory())
            ->fakeContent()
            ->create(
                [
                    'status' => Post::STATUS_SCHEDULED,
                    'scheduled_at' => Carbon::now()->addDays(2)
                ]
            );
    }

    protected function createDraftPost(): Post
    {
        return Post::factory()
            ->hasAttached($this->createCategory())
            ->fakeContent()
            ->create(
                [
                    'status' => Post::STATUS_DRAFT
                ]
            );
    }

    protected function createPublishedPage(): Page
    {
        return Page::factory()
            ->hasTranslations(1, [
                'status' => PageTranslation::STATUS_PUBLISHED,
            ])
            ->create();
    }

    protected function createDraftPage(): Page
    {
        return Page::factory()
            ->hasTranslations(1, [
                'status' => PageTranslation::STATUS_DRAFT,
            ])
            ->create();
    }
}
