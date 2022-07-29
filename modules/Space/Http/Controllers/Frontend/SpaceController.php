<?php

namespace Modules\Space\Http\Controllers\Frontend;

use App\Services\TranslationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Modules\Space\Entities\Page;
use Modules\Space\Entities\PageTranslation;
use Modules\Space\Entities\Space;
use Modules\Space\Exceptions\PageNotFoundException;

class SpaceController extends Controller
{
    use AuthorizesRequests;

    private $locale;

    public function __construct()
    {
        $this->locale = app(TranslationService::class)->currentLanguage();
    }

    public function show(Request $request, PageTranslation $pageTranslation)
    {
        $page = $pageTranslation->page;

        if ($this->canAccessPage($page)) {
            try {
                if (
                    $request->exists('preview')
                    && $request->user()->can('managePage', Space::class)
                ) {
                    return $this->showPreview($page);
                }

                return $this->showGuest($page);

            } catch (PageNotFoundException $exception) {

                return $this->notFoundHandler();

            } catch (\Throwable $th) {

                return $this->notFoundHandler();

            }
        }

        return $this->notFoundHandler();
    }

    private function canAccessPage(Page $page): bool
    {
        $space = $page->space;

        return (
            $space
            && $space->is_page_enabled
        );
    }

    private function showPreview(Page $page)
    {
        $this->authorize('update', $page->space);

        $pageTranslation = $page->translate($this->locale);

        return $this->showPage($pageTranslation);
    }

    private function showGuest(Page $page)
    {
        $pageTranslation = $page->translate($this->locale);

        if (
            $pageTranslation
            && $pageTranslation->status !== PageTranslation::STATUS_DRAFT
        ) {
            return $this->showPage($pageTranslation);
        }

        $defaultLocale = app(TranslationService::class)->getDefaultLocale();
        $pageTranslation = $page->translate($defaultLocale);

        if (
            $pageTranslation
            && $pageTranslation->status !== PageTranslation::STATUS_DRAFT
        ) {
            return $this->showPage($pageTranslation);
        }

        throw new PageNotFoundException();
    }

    private function showPage(PageTranslation $pageTranslation)
    {
        $renderResponse = $this->getViewName($pageTranslation);

        if (!$renderResponse) {
            $renderResponse = $this->getBuilder($pageTranslation);
        }

        if (!$renderResponse) {
            $renderResponse = $this->getFallbackSpace($pageTranslation);
        }

         return $renderResponse;
    }

    private function getViewName(PageTranslation $pageTranslation)
    {
        $viewNameTemplates= [
            'page-{moduleName}_id_{id}-{lang}',
            'page-{moduleName}_id_{id}',
            'page-{moduleName}_slug_{slug}-{lang}',
            'page-{moduleName}_slug_{slug}',
        ];

        $swapText = [
            '{id}' => $pageTranslation->page_id,
            '{lang}' => $pageTranslation->locale,
            '{slug}' => $pageTranslation->slug,
            '{moduleName}' => $this->textToLowerKebab(config('space.name')),
        ];

        foreach ($viewNameTemplates as $template) {
            $viewName = Str::swap($swapText, $template);

            if (view()->exists($viewName)) {
                return view($viewName, $this->getLandingPageData($pageTranslation));
            };
        }

        return null;
    }

    private function getBuilder(PageTranslation $pageTranslation)
    {
        $data = json_decode($pageTranslation->data, true);

        if (
            $data['structures']
            && $data['entities']
        ) {
            // page builder code
        }

        return null;
    }

    private function getFallbackSpace(PageTranslation $pageTranslation)
    {
        $space = $pageTranslation->page->space;

        $viewNameTemplates= [
            'page-{moduleName}_type_{type}-{lang}',
            'page-{moduleName}_type_{type}',
            'page-{moduleName}-{lang}',
            'page-{moduleName}',
        ];

        $type = $space->type ? $this->textToLowerKebab($space->type->name) : null;
        $swapText = [
            '{type}' => $type,
            '{lang}' => $pageTranslation->locale,
            '{moduleName}' => $this->textToLowerKebab(config('space.name')),
        ];

        foreach ($viewNameTemplates as $template) {
            $viewName = Str::swap($swapText, $template);

            if (view()->exists($viewName)) {
                return view($viewName, $this->getLandingPageData($pageTranslation));
            };
        }

        return null;
    }

    private function getLandingPageData(
        PageTranslation $pageTranslation,
        array $additionalData = []
    ): array {
        return array_merge([
            'space' => $pageTranslation->page->space,
            'metaTitle' => $pageTranslation->meta_title,
            'metaDescription' => $pageTranslation->meta_description,
        ], $additionalData);
    }

    private function notFoundHandler()
    {
        return redirect()->route('homepage');
    }

    private function textToLowerKebab(string $text): Stringable
    {
        return Str::of($text)->lower()->kebab();
    }
}
