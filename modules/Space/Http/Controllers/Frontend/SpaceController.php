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
use Modules\Space\ModuleService;
use Modules\Space\Services\SpaceService;
use Modules\Space\Services\PageSpaceService;

class SpaceController extends Controller
{
    use AuthorizesRequests;

    private $locale;
    private $page;

    public function __construct()
    {
        $this->locale = app(TranslationService::class)->currentLanguage();
    }

    public function show(Request $request, $slugs)
    {
        $slugs = collect(explode('/', $slugs));

        $pageTranslation = app(PageSpaceService::class)->getPageTranslationFromRequest();

        if (! $pageTranslation) {
            return $this->notFoundHandler();
        }

        $this->page = $pageTranslation->page;

        return $this->showDetail($request, $pageTranslation);
    }

    public function index()
    {
        return view('spaces', [
            'defaultLogoUrl' => ModuleService::defaultLogoUrl(),
            'metaDescription' => __('Space lists'),
            'metaTitle' => __('Spaces'),
            'spaces' => app(SpaceService::class)->getTopParents(),
        ]);
    }

    private function showDetail(Request $request, PageTranslation $pageTranslation)
    {
        if ($this->canAccessPage($this->page)) {
            try {
                if (
                    $request->exists('preview')
                    && $request->user()->can('managePage', Space::class)
                ) {
                    return $this->showPreview($pageTranslation);
                }

                return $this->showGuest($pageTranslation);

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

    private function showPreview(PageTranslation $pageTranslation)
    {
        $this->authorize('update', $this->page->space);

        if ($pageTranslation->locale != $this->locale) {
            $pageTranslation = $this->page->translate($this->locale);
        }

        return $this->showPage($pageTranslation);
    }

    private function showGuest(PageTranslation $pageTranslation)
    {
        $slug = $pageTranslation->slug;

        if ($pageTranslation->locale != $this->locale) {
            $pageTranslation = $this->page->translate($this->locale, true);
        }

        if (
            $pageTranslation
            && !$pageTranslation->isDraft
        ) {
            return $this->redirectOrShowPage($pageTranslation, $slug);
        }

        throw new PageNotFoundException();
    }

    private function redirectOrShowPage(
        PageTranslation $pageTranslation,
        string $oldSlug
    ) {
        if ($pageTranslation->slug === $oldSlug) {
            return $this->showPage($pageTranslation);
        }

        return redirect()->route('frontend.spaces.show', [
            $pageTranslation->slug
        ]);
    }

    private function showPage(PageTranslation $pageTranslation)
    {
        $renderResponse = $this->getViewName($pageTranslation);

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
            'dateFormat' => config('constants.format.date_time_minute'),
            'metaDescription' => $pageTranslation->meta_description,
            'metaTitle' => $pageTranslation->meta_title,
            'space' => $pageTranslation->page->space,
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
