<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RoleController extends CrudController
{
    private $roleService;

    protected $baseRouteName = 'admin.roles';
    protected $title = 'Role';

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;

        $this->authorizeResource(Role::class, 'role');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Inertia::render('Role/Index', $this->getData([
            'pageNumber' => $request->page,
            'pageQueryParams' => array_filter($request->only('term')),
            'records' => $this->roleService->getRecords(
                $request->term,
                $this->recordsPerPage,
            ),
            'title' => Str::plural($this->title),
        ]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Role/Create', $this->getData([
            'permissions' => $this->roleService->getPermissionOptions(),
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
            ],
        ]);

        $role = new Role();

        $role->saveFromInputs($validated);

        $role->syncPermissions($request->permissions);

        $this->generateFlashMessage('Role created successfully!');

        return redirect()->back();
    }

    public function show(Role $role)
    {
        //
    }

    public function edit(Role $role)
    {
        $role->load('permissions');

        return Inertia::render('Role/Edit', [
            'baseRouteName' => $this->baseRouteName,
            'permissions' => $this->roleService->getPermissionOptions(),
            'record' => $role,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        Validator::make($request->all(), [
            'name' => ['required'],
        ])->validate();

        $role->name = $request->name;
        $role->save();

        $role->syncPermissions($request->permissions);

        $this->generateFlashMessage('Role updated successfully!');

        return redirect()->back();
    }

    public function destroy(Request $request, Role $role)
    {
        $role->delete();

        $this->generateFlashMessage('Role deleted successfully!');

        return redirect()->back();
    }
}
