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
    protected $title = "Categories";

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
        ]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Category/Create', [
            'record' => new $this->model,
            'baseRoute' => $this->baseRouteName,
        ]);
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
        return Inertia::render('Category/Edit', [
            'record' => $category,
            'baseRoute' => $this->baseRouteName,
        ]);
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
