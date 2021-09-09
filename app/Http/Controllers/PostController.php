<?php

namespace App\Http\Controllers;

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
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Post/Index', [
            'records' => $this->postService->getRecords(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Post/Create', [
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
    public function store(Request $request)
    {
        $post = new Post();
        $data = $request->only([
            'locale',
            'title',
            'slug',
            'content',
            'excerpt',
            'meta_title',
            'meta_description',
            'cover_image_id',
            'status',
            'scheduled_on',
        ]);
        $post->fill($data);
        $post->author_id = auth()->id();
        $post->save();

        if (!empty($request->input('categories'))) {
            $post->categories()->attach($request->input('categories'));
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        return Inertia::render('Post/Edit', [
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
    public function update(Request $request, Post $post)
    {
        $post->fill($request->only([
            'locale',
            'title',
            'slug',
            'content',
            'excerpt',
            'meta_title',
            'meta_description',
            'cover_image_id',
            'status',
            'scheduled_on',
        ]));
        $post->save();

        if (!empty($request->input('categories'))) {
            $post->categories()->sync($request->input('categories'));
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
