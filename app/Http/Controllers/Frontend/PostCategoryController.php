<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\PostService;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostCategoryController extends Controller
{
    protected $baseComponentName = 'Post/Frontend';
    protected $baseRouteName = 'blog.category';
    protected $currentLanguage;
    protected $postService;

    public function __construct(
        PostService $postService,
        TranslationService $translationService
    ) {
        $this->postService = $postService;
        $this->translationService = $translationService;
    }

    public function index(Request $request)
    {
        return Inertia::render($this->baseComponentName.'/Index', [
            'baseRouteName' => $this->baseRouteName,
            'pageQueryParams' => array_filter($request->only(
                'term',
                'view',
                'status',
                'slug'
            )),
            'pageNumber' => $request->page,
            'currentLanguage' => $this->translationService->currentLanguage(),
            'records' => $this->postService->getBlogRecords(
                $request->term ?? '',
                10,
                $this->translationService->currentLanguage(),
                $request->slug ?? null,
            ),
        ]);
    }
}
