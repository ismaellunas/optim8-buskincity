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
use App\Services\{
    CountryService,
    LanguageService,
    UserService,
};
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
        $referenceLanguage = app(LanguageService::class)->getReferenceLanguage();

        $record = new User();
        $record->language_id = $referenceLanguage ? $referenceLanguage->id : null;

        return Inertia::render('User/Create', $this->getData([
            'record' => $record,
            'roleOptions' => $this->userService->getRoleOptions(),
            'languageOptions' => app(LanguageService::class)->getSupportedLanguageOptions(),
            'countryOptions' => app(CountryService::class)->getCountryOptions(),
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
        $inputs = $request->validated();

        $user = User::factory()->create([
            'first_name' => $inputs['first_name'],
            'last_name' => $inputs['last_name'],
            'email' => $inputs['email'],
            'country_code' => $inputs['country_code'],
            'language_id' => $inputs['language_id'],
        ]);

        $user->savePassword($request->password);

        $user->verifiyEmail();

        if ($request->hasFile('photo')) {
            $user->updateProfilePhoto($request->file('photo'));
        }

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

        $user->append('isSuperAdministrator', 'profilePageUrl');

        $user->roles->makeHidden('pivot');

        return Inertia::render('User/Edit', $this->getData([
            'can' => [
                'public_profile' => $user->can('public_page.profile'),
            ],
            'record' => $user,
            'roleOptions' => $this->userService->getRoleOptions(),
            'languageOptions' => app(LanguageService::class)->getSupportedLanguageOptions(),
            'countryOptions' => app(CountryService::class)->getCountryOptions(),
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
        if ($request->hasFile('photo')) {
            $user->updateProfilePhoto($request->file('photo'));
        } else if (
            $request->profile_photo_media_id == null
            && $user->profile_photo_media_id != null
        ) {
            $user->deleteProfilePhoto();
        }

        $user->saveFromInputs($request->only([
            'first_name',
            'last_name',
            'email',
            'country_code',
            'language_id',
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
            $this->userService->reassignResources(
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

    public function suspend(User $user)
    {
        $this->authorize('suspend', $user);

        $user->suspend();

        $this->generateFlashMessage('User suspend successfully!');

        return back();
    }

    public function unsuspend(User $user)
    {
        $this->authorize('unsuspend', $user);

        $user->unsuspend();

        $this->generateFlashMessage('User unsuspend successfully!');

        return back();
    }
}
