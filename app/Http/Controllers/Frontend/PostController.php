<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PostService;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PostController extends Controller
{
    protected $baseComponentName = 'Post/Frontend';
    protected $baseRouteName = 'blog';
    protected $currentLanguage;
    protected $postService;

    public function __construct(PostService $postService, TranslationService $translationService)
    {
        $this->postService = $postService;
        $this->translationService = $translationService;
    }

    public function index(Request $request)
    {
        return Inertia::render($this->baseComponentName.'/Index', [
            'pageQueryParams' => array_filter($request->only('term', 'view', 'status')),
            'pageNumber' => $request->page,
            'currentLanguage' => $this->translationService->currentLanguage(),
            'records' => $this->postService->getBlogRecords(
                $request->term ?? '',
                10,
                $this->translationService->currentLanguage()
            ),
        ]);
    }

    public function show(string $locale, string $slug)
    {
        $post = Post::where('slug', $slug)
            ->alreadyPublishedAt(Carbon::now('UTC')->toDateTimeString())
            ->where('locale', $locale)
            ->first();

        if (!$post) {
            return redirect()->route($this->baseRouteName.'.index', ['locale'=>$locale]);
        }

        return Inertia::render($this->baseComponentName.'/Show', [
            'currentLanguage' => $this->translationService->currentLanguage(),
            'post' => $post,
        ]);
    }
}
