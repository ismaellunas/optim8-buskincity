<?php

namespace App\Http\Controllers;

use App\Http\Requests\PageRequest;
use App\Entities\Enums\PageSettingLayout;
use App\Models\{
    Page,
    PageTranslation,
};
use App\Services\{
    MediaService,
    MenuService,
    PageService,
    StorageService,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PageController extends CrudController
{

    protected $model = Page::class;
    protected $baseRouteName = 'admin.pages';
    protected $pageService;
    protected $title = "Page";

    public function __construct(PageService $pageService)
    {
        $this->authorizeResource(Page::class, 'page');
        $this->pageService = $pageService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        return Inertia::render('Page/Index', $this->getData([
            'can' => [
                'add' => $user->can('page.add'),
                'delete' => $user->can('page.delete'),
                'edit' => $user->can('page.edit'),
                'read' => $user->can('page.read'),
            ],
            'pageQueryParams' => array_filter($request->only('term')),
            'records' => $this->pageService->getRecords(
                $request->term,
                $this->recordsPerPage,
            ),
            'defaultLocale' => defaultLocale(),
            'title' => $this->getIndexTitle(),
            'i18n' => $this->translationIndexPage(),
        ]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = auth()->user();

        return Inertia::render('Page/Create', $this->getData([
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
                    'read' => $user->can('media.read'),
                    'edit' => $user->can('media.edit'),
                    'add' => $user->can('media.add'),
                    'delete' => $user->can('media.delete'),
                ],
            ],
            'page' => new $this->model,
            'statusOptions' => $this->model::getStatusOptions(),
            'maxLength' => [
                'meta_title' => config('constants.max_length.meta_title'),
                'meta_description' => config('constants.max_length.meta_description'),
            ],
            'title' => $this->getCreateTitle(),
            'media' => [
                'default_latest_post' => StorageService::getImageUrl(
                    config('constants.default_images.pb_latest_post')
                ),
                'default_video' => StorageService::getImageUrl(
                    config('constants.default_images.pb_video')
                )
            ],
            'settingOptions' => [
                'templates' => PageSettingLayout::options(),
            ],
            'i18n' => $this->translationCreateEditPage(),
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PageRequest $request)
    {
        $inputs = $request->all();
        $page = new $this->model;

        $page->saveFromInputs($inputs);
        $page->saveAuthorId(Auth::id());
        $page->syncMediaFromInputs($inputs);

        $this->generateFlashMessage('The :resource was created!', [
            'resource' => __('Page')
        ]);

        return redirect()->route('admin.pages.edit', $page->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function edit(Page $page)
    {
        $page->load('translations');

        $images = $this->pageService->getImagesFromPage($page);

        $user = auth()->user();

        return Inertia::render('Page/Edit', $this->getData([
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
                    'read' => $user->can('media.read'),
                    'edit' => $user->can('media.edit'),
                    'add' => $user->can('media.add'),
                    'delete' => $user->can('media.delete'),
                ],
                'page' => [
                    'read' => $user->can('page.read'),
                    'delete' => $user->can('page.delete'),
                ],
            ],
            'page' => $page,
            'entityId' => $page->id,
            'statusOptions' => $this->model::getStatusOptions(),
            'images' => $images,
            'maxLength' => [
                'meta_title' => config('constants.max_length.meta_title'),
                'meta_description' => config('constants.max_length.meta_description'),
            ],
            'title' => $this->getEditTitle(),
            'media' => [
                'default_latest_post' => StorageService::getImageUrl(
                    config('constants.default_images.pb_latest_post')
                ),
                'default_video' => StorageService::getImageUrl(
                    config('constants.default_images.pb_video')
                )
            ],
            'settingOptions' => [
                'templates' => PageSettingLayout::options(),
            ],
            'i18n' => $this->translationCreateEditPage(),
        ]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function update(PageRequest $request, Page $page)
    {
        $inputs = $this->pageService->filterInputs(
            $request->all()
        );

        $page->saveFromInputs($inputs);
        $page->syncMediaFromInputs($inputs);

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Page')
        ]);

        return redirect()->route($this->baseRouteName.'.edit', $page->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Page $page)
    {
        $page->delete();

        $this->generateFlashMessage('The :resource was deleted!', [
            'resource' => __('Page')
        ]);

        return redirect()->route('admin.pages.index');
    }

    public function translationDestroy(
        Request $request,
        PageTranslation $pageTranslation
    ) {
        $this->authorize('delete', $pageTranslation->page);

        $pageTranslation->delete();

        $this->generateFlashMessage('Page translation deleted successfully!');
        $this->generateFlashMessage('The :resource was deleted!', [
            'resource' => __('Page Translation')
        ]);

        return redirect()->back();
    }

    public function isUsedByMenus(Page $page, ?string $locale = null)
    {
        return app(MenuService::class)->isModelUsedByMenu($page, $locale);
    }

    public function duplicatePage(Page $page)
    {
        $duplicatePage = new $this->model;

        $duplicatePage->duplicatePage($page);

        $duplicatePage->saveAuthorId(Auth::id());

        $this->generateFlashMessage('The :resource was duplicated!', [
            'resource' => __('Page')
        ]);

        return redirect()->back();
    }

    private function translationIndexPage(): array
    {
        return [
            'search' => __('Search'),
            'create_new' => __('Create new'),
            'title' => __('Title'),
            'slug' => __('Slug'),
            'status' => __('Status'),
            'meta_title' => __('Meta title'),
            'meta_description' => __('Meta description'),
            'language' => __('Language'),
            'actions' => __('Actions'),
            'are_you_sure' => __('Are you sure?'),
            'remove_page_from_navigation_message' => __('This action will also remove the page on the navigation menu.'),
            'yes' => __('Yes'),
            'duplicate_page' => __('Duplicate :resource', ['resource' => __('Page')]),
            'duplicate_page_message' => __('Are you sure want to duplicate this page?'),
        ];
    }

    private function translationCreateEditPage(): array
    {
        return [
            ...[
                'details' => __('Details'),
                'builder' => __('Builder'),
                'settings' => __('Settings'),
                'title' => __('Title'),
                'slug' => __('Slug'),
                'status' => __('Status'),
                'excerpt' => __('Excerpt'),
                'meta_title' => __('Meta title'),
                'meta_description' => __('Meta description'),
                'cancel' => __('Cancel'),
                'create' => __('Create'),
                'update' => __('Update'),
                'layout' => __('Layout'),
                'main_background_color' => __('Main background color'),
                'page_height' => __('Page height'),
                'default' => __('Default'),
                'duplicate' => __('Duplicate'),
                'remove' => __('Remove'),
                'duplicate_translation' => __('Duplicate :resource', ['resource' => __('Translation')]),
                'select_translation' => __('Select :resource', ['resource' => __('Translation')]),
                'page_preview' => __('Page preview'),
            ],
            ...MediaService::defaultMediaLibraryTranslations(),
        ];
    }
}
