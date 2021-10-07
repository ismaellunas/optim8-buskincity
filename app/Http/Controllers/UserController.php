<?php

namespace App\Http\Controllers;

use App\Actions\Jetstream\DeleteUser;
use App\Http\Requests\{
    UserPasswordRequest,
    UserStoreRequest,
    UserUpdateRequest
};
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends CrudController
{
    private $deleteUser;
    private $userService;

    protected $baseRouteName = 'admin.users';
    protected $title = 'User';

    public function __construct(UserService $userService, DeleteUser $deleteUser)
    {
        $this->userService = $userService;
        $this->deleteUser = $deleteUser;

        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isSuperAdministrator) {
            $records = $this
                ->userService
                ->getRecords($request->term, $this->recordsPerPage);
        } else {
            $records = $this
                ->userService
                ->getNoSuperAdministratorRecords($request->term, $this->recordsPerPage);
        }

        $this->userService->transformRecords($records, $user);

        return Inertia::render('User/Index', $this->getData([
            'can' => [
                'add' => $user->can('user.add'),
                'delete' => $user->can('user.delete'),
                'edit' => $user->can('user.edit'),
            ],
            'pageNumber' => $request->page,
            'pageQueryParams' => array_filter($request->only('term', 'view', 'status')),
            'records' => $records,
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
        return Inertia::render('User/Create', $this->getData([
            'record' => new User(),
            'roleOptions' => $this->userService->getRoleOptions(),
            'title' => $this->getCreateTitle(),
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserStoreRequest $request)
    {
        $user = new User();

        $user->saveFromInputs($request->validated());

        $user->savePassword($request->password);

        if ($request->has('role')) {
            $user->assignRole($request->role);
        }

        $this->generateFlashMessage('User created successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $user->load(['roles' => function ($query) {
            $query->select(['id', 'name']);
            $query->limit(1);
        }]);

        $user->roles->makeHidden('pivot');

        return Inertia::render('User/Edit', $this->getData([
            'record' => $user,
            'roleOptions' => $this->userService->getRoleOptions(),
            'title' => $this->getEditTitle(),
        ]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $user->update($request->only([
            'name',
            'email',
        ]));

        if ($request->role) {
            $role = Role::find($request->role);

            if (!$user->hasRole($role)) {
                $user->syncRoles([]);
                $user->assignRole($request->role);
            }
        } else {
            $user->syncRoles([]);
        }

        $this->generateFlashMessage('User updated successfully!');

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $this->deleteUser->delete($user);

        $this->generateFlashMessage('User deleted successfully!');

        return redirect()->back();
    }

    public function updatePassword(UserPasswordRequest $request, User $user)
    {
        $user->password = $this->userService->hashPassword($request->password);
        $user->save();

        $this->generateFlashMessage('Password updated successfully!');

        return redirect()->back();
    }
}
