<?php

namespace Modules\Space\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\Space\Entities\Space;

class SpaceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $parent = $request->parent;

        $spaces = Space::with('children.children')
            ->when($parent == null, function ($query) {
                $query->whereNull('parent_id');
            })
            ->when($parent !== null, function ($query) use ($parent) {
                $query->where('parent_id', $parent);
            })
            ->get()
            ->toArray();

        return Inertia::render('Space::SpaceIndex', [
            'title' => "Space",
            'spaces' => $spaces,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('space::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('space::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('space::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function moveNode(Request $request, $current, $parent = null)
    {
        $currentSpace = Space::find($current);

        if (! $parent) {
            $currentSpace->parent_id = null;
            $currentSpace->save();

            $currentSpace->updateDepth(0);

        } else {

            $parentSpace = Space::find($parent);
            $currentSpace->parent_id = $parentSpace->id;

            $currentSpace->updateDepth($parentSpace->depth + 1);
        }

        return back();
    }
}
