<?php

namespace App\Http\Controllers\Frontend;

use App\Models\{
    Category,
    CategoryTranslation
};
use App\Http\Controllers\Controller;
use App\Services\PostService;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class PostCategoryController extends Controller
{
    private $baseRouteName = 'blog.category';
    private $perPage = 10;

    private $postService;
    private $translationService;

    public function __construct(
        PostService $postService,
        TranslationService $translationService
    ) {
        $this->postService = $postService;
        $this->translationService = $translationService;
    }

    public function index(
        Request $request,
        CategoryTranslation $categoryTranslation
    ) {
        $locale = $this->translationService->currentLanguage();
        $category = $categoryTranslation->category;

        if (!$category->hasTranslation($locale)) {
            return $this->goToCategoryWithDefaultLocale(
                $request,
                $category,
                $locale,
            );
        } else {
            $newCategoryTranslation = $category->translate($locale);

            if ($newCategoryTranslation->slug !== $categoryTranslation->slug) {
                return redirect()->route($this->baseRouteName.'.index', [
                    $newCategoryTranslation->slug
                ]);
            }

            return view('posts', [
                'searchRoute' => route($this->baseRouteName.'.index', $newCategoryTranslation->slug),
                'pageQueryParams' => array_filter($request->only('term', 'view', 'status')),
                'metaTitle' => (
                    $newCategoryTranslation->meta_title
                    ?? $newCategoryTranslation->name
                ),
                'metaDescription' => (
                    $newCategoryTranslation->meta_description
                    ?? $newCategoryTranslation->name
                ),
                'records' => $this->postService->getBlogRecords(
                    $request->term ?? '',
                    $this->perPage,
                    $this->translationService->currentLanguage(),
                    $category->id
                ),
            ]);
        }

    }

    private function goToCategoryWithDefaultLocale(
        Request $request,
        Category $category,
        string $locale,
    ) {
        $defaultLocale = $this->translationService->getDefaultLocale();

        if ($category->hasTranslation($defaultLocale)) {
            $categoryTranslation = $category->translate($defaultLocale);
        } else {
            $categoryTranslation = $category->translations->first();
        }

        return view('posts', [
            'searchRoute' => route($this->baseRouteName.'.index', $categoryTranslation->slug),
            'pageQueryParams' => array_filter($request->only('term', 'view', 'status')),
            'metaTitle' => (
                $categoryTranslation->meta_title
                ?? $categoryTranslation->name
            ),
            'metaDescription' => (
                $categoryTranslation->meta_description
                ?? $categoryTranslation->name
            ),
            'records' => $this->postService->getBlogRecords(
                $request->term ?? '',
                $this->perPage,
                $locale,
                $category->id
            ),
        ]);
    }
}
