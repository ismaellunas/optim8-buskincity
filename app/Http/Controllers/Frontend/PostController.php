<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PostService;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class PostController extends Controller
{
    private $baseRouteName = 'blog';
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

    public function index(Request $request)
    {
        return view('posts', [
            'searchRoute' => route($this->baseRouteName.'.index'),
            'pageQueryParams' => array_filter($request->only('term', 'view', 'status')),
            'records' => $this->postService->getBlogRecords(
                $request->term ?? '',
                $this->perPage,
                $this->translationService->currentLanguage(),
            ),
            'metaTitle' => __('Blog'),
            'metaDescription' => __('Blog lists'),
        ]);
    }

    public function show(string $slug)
    {
        $locale = $this->translationService->currentLanguage();

        $post = $this->postService->getFirstBySlug($slug, $locale);

        if ($post && $post['status'] != Post::STATUS_PUBLISHED) {

            $user = auth()->user();
            if ($user === null || $user->can('post.read') === false) {
                return redirect()->route($this->baseRouteName.'.index');
            }

        }

        if (!$post) {
            $post = $this->postService->getFirstBySlug($slug);
        }

        if (!$post) {
            return redirect()->route($this->baseRouteName.'.index');
        }

        $publishedOn = null;
        if ($post->published_at) {
            $publishedOn = __('Published on :date', [
                'date' => $post->published_at->format(config('constants.format.date_post')),
            ]);
        }

        return view('post', [
            'currentLanguage' => $this->translationService->currentLanguage(),
            'post' => $post,
            'content' => $this->postService->transformContent($post->purifiedContent),
            'readingTime' => $this->postService->readingTime($post->plain_text_content),
            'tableOfContents' => $this->postService->tableOfContents($post->content),
            'publishedOn' => $publishedOn,
            'lastUpdatedOn' => __('Last updated on :date', [
                'date' => $post->updated_at->format(config('constants.format.date_post')),
            ]),
            'relatedArticles' => $this->postService->getRelatedArticles($post),
        ]);
    }
}
