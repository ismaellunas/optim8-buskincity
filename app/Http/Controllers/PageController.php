<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Media;
use Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Page/Index', [
            'pages' => Page::all(['id','title', 'slug']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Page/Create', [
            'statusOptions' => Page::getStatusOptions(),
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

        $page = new Page();
        $page->title = $request->input('title');
        $page->slug = $request->input('slug');
        $page->excerpt = $request->input('excerpt');
        $page->meta_description = $request->input('meta_description');
        $page->meta_title = $request->input('meta_title');
        $page->status = $request->input('status', Page::STATUS_INACTIVE);
        $page->data = $request->input('data');
        $page->author_id = Auth::id();
        $page->save();

        $request->session()->flash('message', 'Page created successfully!');

        return redirect()->route('admin.pages.edit', [
            'page' => $page->id,
            'tab' => 'builder'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function show(Page $page)
    {
        /* @TODO: update to use slug
        return Inertia::render('PageBuilder/Show', [
            'page' => $page,
        ]);
         */
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function edit(Page $page)
    {
        return Inertia::render('Page/Create', [
            'page' => $page,
            'entityId' => $page->id,
            'statusOptions' => Page::getStatusOptions(),
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
        $this->getValidate($request);

        $page->title = $request->input('title');
        $page->slug = $request->input('slug');
        $page->excerpt = $request->input('excerpt');
        $page->meta_description = $request->input('meta_description');
        $page->meta_title = $request->input('meta_title');
        $page->status = $request->input('status', Page::STATUS_INACTIVE);
        $page->data = $request->input('data');
        $page->save();

        $request->session()->flash('message', 'Page updated successfully!');

        return redirect()->route('admin.pages.index');
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

    private function getValidate(Request $request, $id = null): void
    {
        $data = [
            'title' => 'required',
            'slug' => 'required|alpha_dash|unique:pages,slug,'.$id
        ];
        $this->validate($request, $data);
    }
}
