<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostIndexRequest;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Services\MediaService;
use App\Services\PostService;
use App\Traits\HasModuleViewData;
use Inertia\Inertia;

class PostController extends CrudController
{
    use HasModuleViewData;

    protected $postService;
    protected $baseRouteName = 'admin.posts';
    protected $title = 'Post';

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
        $this->authorizeResource(Post::class, 'post');
    }

    public function index(PostIndexRequest $request)
    {
        $user = auth()->user();

        return Inertia::render('Post/Index', $this->getData([
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
            'title' => $this->getIndexTitle(),
            'i18n' => [
                'search' => __('Search'),
                'create_new' => __('Create New'),
                'published' => __('Published'),
                'scheduled' => __('Scheduled'),
                'draft' => __('Draft'),
                'filter' => __('Filter'),
                'category' => __('Category'),
                'language' => __('Language'),
                'are_you_sure' => __('Are you sure you want to delete this resource?'),
            ],
        ]));
    }

    public function create()
    {
        $user = auth()->user();

        return Inertia::render('Post/Create', $this->getData(
            array_merge_recursive(
                [
                    'breadcrumbs' => [
                        [
                            'title' => $this->getIndexTitle(),
                            'url' => route($this->baseRouteName.'.index'),
                        ],
                        [
                            'title' => $this->getCreateTitle(),
                        ],
                    ],
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
                    'languageOptions' => $this->postService->getLanguageOptions(),
                    'post' => new Post(),
                    'statusOptions' => Post::getStatusOptions(),
                    'maxLength' => [
                        'meta_title' => config('constants.max_length.meta_title'),
                        'meta_description' => config('constants.max_length.meta_description'),
                    ],
                    'title' => $this->getCreateTitle(),
                    'instructions' => [
                        'mediaLibrary' => MediaService::defaultMediaLibraryInstructions(),
                    ],
                    'i18n' => $this->getI18nCreateEditPage(),
                ],
                $this->getModulesViewData()
            )
        ));
    }

    public function store(PostRequest $request)
    {
        $post = new Post();

        $post->saveFromInputs($request->only([
            'content',
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
            $post->syncCategories(
                $request->input('categories'),
                $request->input('primary_category')
            );
        }

        $post->syncMedia([
            $request->input('cover_image_id')
        ]);

        $this->generateFlashMessage('The :resource was created!', [
            'resource' => __('Post')
        ]);

        return redirect()->route($this->baseRouteName.'.edit', $post->id);
    }

    public function edit(Post $post)
    {
        $user = auth()->user();

        return Inertia::render('Post/Edit', $this->getData(
            array_merge_recursive(
                [
                    'breadcrumbs' => [
                        [
                            'title' => $this->getIndexTitle(),
                            'url' => route($this->baseRouteName.'.index'),
                        ],
                        [
                            'title' => $this->getEditTitle(),
                        ],
                    ],
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
                    'languageOptions' => $this->postService->getLanguageOptions($post),
                    'post' => $post->load('categories'),
                    'statusOptions' => Post::getStatusOptions(),
                    'maxLength' => [
                        'meta_title' => config('constants.max_length.meta_title'),
                        'meta_description' => config('constants.max_length.meta_description'),
                    ],
                    'title' => $this->getEditTitle(),
                    'instructions' => [
                        'mediaLibrary' => MediaService::defaultMediaLibraryInstructions(),
                    ],
                    'i18n' => $this->getI18nCreateEditPage(),
                ],
                $this->getModulesViewData()
            )
        ));
    }

    public function update(PostRequest $request, Post $post)
    {
        $inputs = $request->only([
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
        ]);

        $post->saveFromInputs($inputs);

        if ($request->has('categories')) {
            $post->syncCategories(
                $request->input('categories'),
                $request->input('primary_category')
            );
        }

        $post->syncMedia([
            $request->input('cover_image_id')
        ]);

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Post')
        ]);

        return redirect()->route($this->baseRouteName.'.edit', $post->id);
    }

    public function destroy(Post $post)
    {
        $post->delete();

        $this->generateFlashMessage('The :resource was deleted!', [
            'resource' => __('Post')
        ]);

        return redirect()->back();
    }

    private function getI18nCreateEditPage(): array
    {
        return [
            'content' => __('Content'),
            'seo' => __('SEO'),
            'title' => __('Title'),
            'slug' => __('Slug'),
            'language' => __('Language'),
            'category' => __('Category'),
            'select_primary_category' => __('Select The Primary Category'),
            'thumbnail' => __('Thumbnail'),
            'excerpt' => __('Excerpt'),
            'status' => __('Status'),
            'publish_options' => __('Publish Options'),
            'scheduled_at' => __('Scheduled at'),
            'open_media' => __('Open Media'),
            'remove' => __('Remove'),
            'meta_title' => __('Meta Title'),
            'meta_description' => __('Meta Description'),
            'create' => __('Create'),
            'update' => __('Update'),
            'cancel' => __('Cancel'),
        ];
    }
}
