<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
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
            'title' => $this->getCreateTitle(),
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleRequest $request)
    {
        $role = new Role();

        $role->saveFromInputs($validated);

        $role->syncPermissions($request->permissions);

        $this->generateFlashMessage('Role created successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    public function show(Role $role)
    {
        //
    }

    public function edit(Role $role)
    {
        $role->load('permissions');

        return Inertia::render('Role/Edit', $this->getData([
            'permissions' => $this->roleService->getPermissionOptions(),
            'record' => $role,
            'title' => $this->getEditTitle(),
        ]));
    }

    public function update(RoleRequest $request, Role $role)
    {
        $role->name = $request->name;
        $role->save();

        $role->syncPermissions($request->permissions);

        $this->generateFlashMessage('Role updated successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    public function destroy(Request $request, Role $role)
    {
        $role->delete();

        $this->generateFlashMessage('Role deleted successfully!');

        return redirect()->back();
    }
}
