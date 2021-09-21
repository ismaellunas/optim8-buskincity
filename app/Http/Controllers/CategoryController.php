<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Astrotomic\Translatable\Validation\RuleFactory;
use App\Services\TranslationService;

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
            'baseRoute' => 'admin.categories',
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
        $validatedData = $this->getValidate($request);

        $record = new $this->model;
        $record->fill($validatedData);
        $record->save();

        $request->session()->flash('message', 'Category created successfully!');

        return redirect()->route('admin.categories.index');
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
            'baseRoute' => 'admin.categories',
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $validatedData = $this->getValidate($request);


        $providedLocales = array_keys($category->getTranslationsArray());
        $validatedLocales = array_keys($validatedData);

        $category->fill($validatedData);
        $category->save();

        $unusedLocales = array_diff($providedLocales, $validatedLocales);
        if (!empty($unusedLocales)) {
            $category->deleteTranslations($unusedLocales);
        }

        $request->session()->flash('message', 'Category updated successfully!');

        return redirect()->route('admin.categories.index');
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

        $request->session()->flash('message', 'Category deleted successfully!');

        return redirect()->route('admin.categories.index');
    }

    protected function getRecords()
    {
        return $this->model::orderBy('id', 'DESC')
            ->paginate($this->recordsPerPage);
    }

    protected function getValidate(Request $request, $id = null)
    {
        $rules = RuleFactory::make([
            '%name%' => 'sometimes|string',
        ]);

        return $request->validate($rules);
    }
}
