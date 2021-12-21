<?php

namespace App\Http\Controllers;

use App\Actions\Jetstream\DeleteUser;
use App\Http\Requests\{
    UserDestroyRequest,
    UserPasswordRequest,
    UserStoreRequest,
    UserUpdateRequest
};
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
            $records = $this->userService->getRecords(
                $request->term,
                $this->recordsPerPage,
                ['inRoles' => $request->roles ?? null]
            );
        } else {
            $records = $this->userService->getNoSuperAdministratorRecords(
                $request->term,
                $this->recordsPerPage,
                ['inRoles' => $request->roles ?? null]
            );
        }

        $this->userService->transformRecords($records, $user);

        return Inertia::render('User/Index', $this->getData([
            'can' => [
                'add' => $user->can('user.add'),
                'delete' => $user->can('user.delete'),
                'edit' => $user->can('user.edit'),
            ],
            'pageNumber' => $request->page,
            'pageQueryParams' => array_filter($request->only('term', 'view', 'roles')),
            'records' => $records,
            'roleOptions' => $this->userService->getRoleOptions(),
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

        $user->verifiyEmail();

        if ($request->has('role')) {
            $user->assignRole($request->role);
        }

        $this->generateFlashMessage('User created successfully!');

        return redirect()->route($this->baseRouteName.'.index');
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

        $user->append('isSuperAdministrator');

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
        $user->saveFromInputs($request->only([
            'first_name',
            'last_name',
            'email',
        ]));

        if (! $user->isSuperAdministrator) {

            if (! $request->role) {

                $user->roles()->detach();

            } elseif (! $user->hasRole($request->role)) {

                $user->roles()->detach();
                $user->assignRole($request->role);
            }

            $user->forgetCachedPermissions();
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
    public function destroy(UserDestroyRequest $request, User $user)
    {
        if ($request->is_reassigned) {
            $this->userService->delegateResources(
                $user->id,
                $request->assigned_user
            );
        }

        $this->deleteUser->delete($user);

        $this->generateFlashMessage('User deleted successfully!');

        return redirect()->back();
    }

    public function updatePassword(UserPasswordRequest $request, User $user)
    {
        $user->savePassword($request->password);

        $this->generateFlashMessage('Password updated successfully!');

        return redirect()->back();
    }

    public function getReassignmentCandidates(Request $request, User $user)
    {
        return User::when($request->term, function ($query, $request) {
                $query->search($request->term);
            })
            ->where('id', '<>', $user->id)
            ->get([
                'id',
                'email',
                'first_name',
                'last_name',
            ]);
    }
}
