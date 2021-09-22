<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends CrudController
{
    protected $model = Category::class;
    protected $baseRouteName = 'admin.categories';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Category/Index', [
            'records' => $this->getRecords(),
        ]);
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
        $record = new $this->model;

        $record->saveFromInputs($request->validated());

        $this->generateFlashMessage('Category created successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //
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
        $validatedData = $request->validated();

        $category->saveFromInputs($validatedData);

        $category->syncTranslations(array_keys($validatedData));

        $this->generateFlashMessage('Category updated successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Category $category)
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
