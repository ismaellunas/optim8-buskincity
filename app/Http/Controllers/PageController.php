<?php

namespace App\Http\Controllers;

use App\Http\Requests\PageRequest;
use App\Models\{
    Media,
    Page,
    PageTranslation,
};
use App\Services\{
    MenuService,
    PageService,
    TranslationService,
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
            'defaultLocale' => TranslationService::getDefaultLocale(),
            'title' => $this->getIndexTitle(),
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
        $page = new $this->model;

        $page->saveFromInputs($request->all());
        $page->saveAuthorId(Auth::id());

        $request->session()->flash('message', 'Page created successfully!');

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
        $images = [];

        $page->load('translations');

        foreach ($page->translations as $translation) {
            if (!empty($translation->data['media'])) {
                $locale = $translation->locale;
                $mediaIds = collect($translation->data['media'])->pluck('id');

                $images[$locale] = Media::whereIn('id', $mediaIds)
                    ->image()
                    ->with([
                        'translations' => function ($q) use ($locale) {
                            $q->select(['id', 'locale', 'alt', 'media_id']);
                            $q->where('locale', $locale);
                        },
                    ])
                    ->default()
                    ->get(['id', 'file_url']);
            }
        }

        $user = auth()->user();

        return Inertia::render('Page/Edit', $this->getData([
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
        $inputs = $request->all();

        $locale = collect($inputs)->pluck('locale')->filter()->first();

        $oldStatus = $page->translate($locale)->status ?? null;

        $pageTranslation = $inputs[$locale] ?? [];

        $page->saveFromInputs($inputs);

        if (
            !empty($pageTranslation['page_id'])
            && $oldStatus == PageTranslation::STATUS_PUBLISHED
            && $pageTranslation['status'] == PageTranslation::STATUS_DRAFT
        ) {
            app(MenuService::class)->removePageFromMenus(
                $pageTranslation['page_id'],
                $pageTranslation['locale']
            );
        }

        $this->generateFlashMessage('Page updated successfully!');

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
        $request->session()->flash('message', 'Page deleted successfully!');
        return redirect()->route('admin.pages.index');
    }

    public function isUsedByMenus(Page $page, ?string $locale = null)
    {
        return app(MenuService::class)->isPageUsedByMenu($page->id, $locale);
    }
}
