<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends CrudController
{
    protected $model = Category::class;
    protected $baseRouteName = 'admin.categories';
    protected $categoryService;
    protected $title = "Category";

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
        $this->authorizeResource(Category::class, 'category');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        return Inertia::render('Category/Index', $this->getData([
            'can' => [
                'add' => $user->can('category.add'),
                'delete' => $user->can('category.delete'),
                'edit' => $user->can('category.edit'),
            ],
            'pageQueryParams' => array_filter($request->only('term')),
            'pageNumber' => $request->page,
            'records' => $this
                ->categoryService
                ->getRecords($request->term, $this->recordsPerPage),
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
        return Inertia::render('Category/Create', $this->getData([
            'breadcrumbs' => [
                [
                    'title' => $this->getIndexTitle(),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                [
                    'title' => $this->getCreateTitle(),
                ],
            ],
            'record' => new $this->model,
            'maxLength' => [
                'meta_title' => config('constants.max_length.meta_title'),
                'meta_description' => config('constants.max_length.meta_description'),
            ],
            'title' => $this->getCreateTitle(),
        ]));
    }

    public function store(CategoryRequest $request)
    {
        $category = new $this->model;
        $inputs = $request->validated();

        $category->saveFromInputs($inputs);

        $this->generateFlashMessage('Category created successfully!');

        return redirect()->route($this->baseRouteName.'.edit', $category->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        return Inertia::render('Category/Edit', $this->getData([
            'breadcrumbs' => [
                [
                    'title' => $this->getIndexTitle(),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                [
                    'title' => $this->getEditTitle(),
                ],
            ],
            'record' => $category,
            'maxLength' => [
                'meta_title' => config('constants.max_length.meta_title'),
                'meta_description' => config('constants.max_length.meta_description'),
            ],
            'title' => $this->getEditTitle(),
        ]));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $inputs = $request->all();

        $category->saveFromInputs($inputs);

        $this->generateFlashMessage('Category updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit', $category->id);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        $this->generateFlashMessage('Category deleted successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    protected function getRecords()
    {
        return $this->model::orderBy('id', 'DESC')
            ->with([
                'translations' => function ($query) {
                    $query->select('id', 'name', 'category_id', 'locale');
                },
            ])
            ->paginate($this->recordsPerPage);
    }
}
