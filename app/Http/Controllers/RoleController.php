<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;
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
            'title' => $this->getIndexTitle(),
            'i18n' => [
                'search' => __('Search'),
                'create_new' => __('Create new'),
                'name' => __('Name'),
                'actions' => __('Actions'),
                'are_you_sure' => __('Are you sure?'),
            ],
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
            'breadcrumbs' => [
                [
                    'title' => $this->getIndexTitle(),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                [
                    'title' => $this->getCreateTitle(),
                ],
            ],
            'permissions' => $this->roleService->getPermissionOptions(),
            'title' => $this->getCreateTitle(),
            'i18n' => $this->translationCreateEditPage(),
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

        $role->saveFromInputs($request->validated());

        $role->syncPermissions($request->permissions);

        $this->generateFlashMessage('The :updated was created!', [
            'resource' => __('Role')
        ]);

        return redirect()->route($this->baseRouteName.'.edit', $role->id);
    }

    public function show(Role $role)
    {
        //
    }

    public function edit(Role $role)
    {
        $role->load('permissions');

        return Inertia::render('Role/Edit', $this->getData([
            'breadcrumbs' => [
                [
                    'title' => $this->getIndexTitle(),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                [
                    'title' => $this->getEditTitle(),
                ],
            ],
            'permissions' => $this->roleService->getPermissionOptions(),
            'record' => $role,
            'title' => $this->getEditTitle(),
            'i18n' => $this->translationCreateEditPage(),
        ]));
    }

    public function update(RoleRequest $request, Role $role)
    {
        $role->name = $request->name;
        $role->save();

        $role->syncPermissions($request->permissions);

        $this->generateFlashMessage('The :updated was deleted!', [
            'resource' => __('Role')
        ]);

        return redirect()->back();
    }

    public function destroy(Request $request, Role $role)
    {
        $role->delete();

        $this->generateFlashMessage('The :resource was deleted!', [
            'resource' => __('Role')
        ]);

        return redirect()->back();
    }

    private function translationCreateEditPage(): array
    {
        return [
            'name' => __('Name'),
            'cancel' => __('Cancel'),
            'create' => __('Create'),
            'update' => __('Update'),
        ];
    }
}
