<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends CrudController
{
    protected $postService;
    protected $baseRouteName = 'admin.posts';

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
        $this->authorizeResource(Post::class, 'post');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        return Inertia::render('Post/Index', [
            'can' => [
                'add' => $user->can('post.add'),
                'delete' => $user->can('post.delete'),
                'edit' => $user->can('post.edit'),
            ],
            'categoryOptions' => $this->postService->getCategoryOptions(),
            'pageQueryParams' => array_filter($request->only(
                'term',
                'view',
                'status',
                'languages',
                'categories'
            )),
            'pageNumber' => $request->page,
            'records' => $this->postService->getRecords(
                $request->term,
                array_filter([
                    $request->status ?? 'published',
                    'inLanguages' => $request->languages,
                    'inCategories' => $request->categories,
                ])
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = auth()->user();

        return Inertia::render('Post/Create', [
            'can' => [
                'media' => [
                    'browse' => $user->can('media.browse'),
                    'read' => $user->can('media.edit'),
                    'edit' => $user->can('media.edit'),
                    'add' => $user->can('media.add'),
                    'delete' => $user->can('media.delete'),
                ],
            ],
            'categoryOptions' => $this->postService->getCategoryOptions(),
            'post' => new Post(),
            'statusOptions' => Post::getStatusOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostRequest $request)
    {
        $post = new Post();

        $post->saveFromInputs($request->only([
            'content',
            'cover_image_id',
            'excerpt',
            'locale',
            'meta_description',
            'meta_title',
            'scheduled_at',
            'slug',
            'status',
            'title',
        ]));

        if ($request->has('categories')) {
            $post->syncCategories($request->input('categories'));
        }

        $this->generateFlashMessage('Post created successfully!');

        return redirect()->route($this->baseRouteName.'.edit', $post->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        $user = auth()->user();

        return Inertia::render('Post/Edit', [
            'can' => [
                'media' => [
                    'browse' => $user->can('media.browse'),
                    'read' => $user->can('media.edit'),
                    'edit' => $user->can('media.edit'),
                    'add' => $user->can('media.add'),
                    'delete' => $user->can('media.delete'),
                ],
            ],
            'categoryOptions' => $this->postService->getCategoryOptions(),
            'coverImage' => $post->coverImage,
            'post' => $post->load('categories'),
            'statusOptions' => Post::getStatusOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(PostRequest $request, Post $post)
    {
        $post->saveFromInputs($request->only([
            'content',
            'cover_image_id',
            'excerpt',
            'locale',
            'meta_description',
            'meta_title',
            'slug',
            'status',
            'title',
            'scheduled_at',
        ]));

        if ($request->has('categories')) {
            $post->syncCategories($request->input('categories'));
        }

        $this->generateFlashMessage('Post updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit', $post->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $post->delete();
        $this->generateFlashMessage('Post deleted successfully!');
        return redirect()->route($this->baseRouteName.'.index');
    }
}
