<?php

namespace Modules\Space\Http\Controllers\Frontend;

use App\Services\TranslationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Space\Entities\Page;
use Modules\Space\Entities\PageTranslation;
use Modules\Space\Exceptions\PageNotFoundException;
use Modules\Space\Services\SpaceService;

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
                if ($request->exists('preview')) {
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
            'page-{id}-{lang}',
            'page-{id}',
            'page-{slug}-{lang}',
            'page-{slug}',
        ];

        $swapText = [
            '{id}' => $pageTranslation->page_id,
            '{lang}' => $pageTranslation->locale,
            '{slug}' => $pageTranslation->slug,
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
        $types = app(SpaceService::class)->types();

        $viewNameTemplates= [
            'page-{type}-{lang}',
            'page-{type}',
            'page-space-{lang}',
            'page-space',
        ];

        $type = $types[$pageTranslation->page->space->type] ?? null;
        $swapText = [
            '{type}' => $type,
            '{lang}' => $pageTranslation->locale,
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
}
