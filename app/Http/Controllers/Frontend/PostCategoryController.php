<?php

namespace App\Http\Controllers\Frontend;

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

    public function index(Request $request, $categoryId)
    {
        return view('posts', [
            'searchRoute' => route($this->baseRouteName.'.index', $categoryId),
            'pageQueryParams' => array_filter($request->only('term', 'view', 'status')),
            'records' => $this->postService->getBlogRecords(
                $request->term ?? '',
                $this->perPage,
                $this->translationService->currentLanguage(),
                $categoryId
            ),
        ]);
    }
}
