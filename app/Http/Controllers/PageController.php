<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Page;
use App\Services\{
    PageService,
    TranslationService,
};
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PageController extends CrudController
{
    private $pageService;

    protected $model = Page::class;
    protected $baseRouteName = 'admin.pages';
    protected $title = "Pages";

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;

        $this->authorizeResource(Page::class, 'page');
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

        return Inertia::render('Page/Create', [
            'can' => [
                'media' => [
                    'browse' => $user->can('media.browse'),
                    'read' => $user->can('media.read'),
                    'edit' => $user->can('media.edit'),
                    'add' => $user->can('media.add'),
                    'delete' => $user->can('media.delete'),
                ]
            ],
            'page' => new $this->model,
            'statusOptions' => $this->model::getStatusOptions(),
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
        $this->getValidate($request);
        $inputs = $request->input('translations', []);

        $page = new $this->model;
        $locale = array_key_first($inputs);
        $pageTranslation = $page->translate($locale);
        $this->getValidate($request, $pageTranslation->id ?? null);

        $page->fill($inputs);
        $page->author_id = Auth::id();

        $page->save();

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

        return Inertia::render('Page/Edit', [
            'can' => [
                'media' => [
                    'browse' => $user->can('media.browse'),
                    'read' => $user->can('media.read'),
                    'edit' => $user->can('media.edit'),
                    'add' => $user->can('media.add'),
                    'delete' => $user->can('media.delete'),
                ]
            ],
            'page' => $page,
            'entityId' => $page->id,
            'statusOptions' => $this->model::getStatusOptions(),
            'images' => $images,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Page $page)
    {
        $locale = array_key_first($request->input('translations', []));
        $pageTranslation = $page->translate($locale);

        $this->getValidate($request, $pageTranslation->id ?? null);

        $page->fill($request->input('translations'));
        $page->save();

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

    protected function getValidate(Request $request, $id = null): array
    {
        $rules = RuleFactory::make([
            '%title%' => 'sometimes|string',
            '%slug%' => [
                'sometimes',
                'required',
                'alpha_dash',
                'unique:page_translations,slug'.($id ? ",{$id}" : "")
            ],
        ]);

        $inputs = $request->input('translations', []);
        $messages = [];
        $locales = array_keys($inputs);
        $attributes = ['title', 'slug'];
        $inputs = $request->input('translations');

        $customAttributes = $this->generateCustomAttributes($locales, $attributes);

        $validator = $this->getValidationFactory()
             ->make($inputs, $rules, $messages, $customAttributes);
        return $validator->validate();
    }

    public function generateCustomAttributes($locales, $attributes)
    {
        $translatedAttributes = [];
        foreach ($locales as $locale) {
            foreach ($attributes as $attribute) {
                $attributeKey = $locale.'.'.$attribute;
                $translatedAttributes[$attributeKey] = (
                    Str::title($attribute).
                    " (".TranslationService::getLanguageFromLocale($locale).")"
                );
            }
        }
        return $translatedAttributes;
    }
}
