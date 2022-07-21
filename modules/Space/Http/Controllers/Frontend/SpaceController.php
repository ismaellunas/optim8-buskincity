<?php

namespace Modules\Space\Http\Controllers\Frontend;

use App\Models\PageTranslation;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Space\Services\SpaceService;

class SpaceController extends Controller
{
    private $locale;

    public function __construct()
    {
        $this->locale = app(TranslationService::class)->currentLanguage();
    }

    public function show(PageTranslation $pageTranslation)
    {
        $viewName = null;

        if ($pageTranslation->page->space->is_page_enabled) {
            $this->reTranslatePage($pageTranslation);

            $viewName = $this->getViewNameBasedOnPage($pageTranslation);
        }

        if (
            !$viewName
            && $pageTranslation->page->space->type
        ) {
            $viewName = $this->getViewNameBasedOnType(
                $pageTranslation->page->space->type
            );
        }

        $data = [
            'space' => $pageTranslation->page->space,
            'metaTitle' => $pageTranslation->meta_title,
            'metaDescription' => $pageTranslation->meta_description,
        ];

        if ($viewName) {
            return view($viewName, $data);
        }

        return view($this->viewFallback(), $data);
    }

    private function reTranslatePage(PageTranslation &$pageTranslation): void
    {
        $page = $pageTranslation->page;

        if ($pageTranslation->page->hasTranslation($this->locale)) {
            $pageTranslation = $page->translate($this->locale);
        } else {
            $pageTranslation = $page->translate(
                app(TranslationService::class)->getDefaultLocale()
            );
        }
    }

    private function getViewNameBasedOnPage(PageTranslation $pageTranslation): ?string
    {
        if (
            $pageTranslation->status != PageTranslation::STATUS_PUBLISHED
            && !$this->userCanAccessPage()
        ) {
            return null;
        }

        $viewName = $this->getViewName($pageTranslation->slug, $this->locale);

        if ($this->isViewExists($viewName)) {
            return $viewName;
        }

        return null;
    }

    private function getViewNameBasedOnType(int $type): ?string
    {
        $types = app(SpaceService::class)->types();
        $type = Str::lower($types[$type]);

        $viewName = $this->getViewName($type, $this->locale);

        if ($this->isViewExists($viewName)) {
            return $viewName;
        }

        $viewName = $this->getViewName($type);

        if ($this->isViewExists($viewName)) {
            return $viewName;
        }

        return null;
    }

    private function getViewName(string $prefix, string $locale = null): string
    {
        return 'page' . '-' . $prefix . ($locale ? '-' . $locale : null);
    }

    private function viewFallback(): string
    {
        return 'page-space';
    }

    private function isViewExists($viewName): bool
    {
        return view()->exists($viewName);
    }

    private function userCanAccessPage(): bool
    {
        if (Auth::check()) {
            return Auth::user()->can('page.read');
        } else {
            return false;
        }
    }
}
