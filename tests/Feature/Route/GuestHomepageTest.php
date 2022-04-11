<?php

namespace Tests\Feature\Route;

use App\Models\Language;
use App\Services\TranslationService;

class GuestHomepageTest extends BaseRouteTestCase
{
    private $routeName = 'homepage';

    /**
     * @test
     */
    public function guestCanAccessHomepage()
    {
        // Act
        $response = $this->get(
            route($this->routeName)
        );

        // Assert
        $this->assertGuest();
        $response->assertOk();
    }

    /**
     * @test
     */
    public function guestCanRenderTheHomepageView()
    {
        // Act
        $response = $this->get(
            route($this->routeName)
        );

        // Assert
        $response->assertViewIs('home');
    }

    /**
     * @TODO
     * Cannot trigger the prefix locale on route
     */
    public function guestCanAccessActiveLanguage()
    {
        // Arrange
        $locales = TranslationService::getLocales();

        // Act
        foreach ($locales as $locale) {
            $this->refreshApplicationWithLocale($locale);

            $response = $this->get(
                route($this->routeName)
            );

            // Assert
            $this->assertGuest();
            $response->assertOk();
        }

    }

    /**
     * @TODO
     * Cannot trigger the prefix locale on route
     */
    public function guestCannotAccessUnactiveLanguage()
    {
        // Arrange
        $unactiveLocale = $this->getUnactiveLanguage();

        // Act
        $this->refreshApplicationWithLocale($unactiveLocale);

        $response = $this->get(
            route($this->routeName)
        );

        // Assert
        $this->assertGuest();
        $response->assertOk();

    }

    private function getUnactiveLanguage(): ?string
    {
        $language = Language::where('is_active', false)->first();

        return $language ? $language->code : null;
    }
}
