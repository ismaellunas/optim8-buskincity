<?php

namespace App\Http\Controllers;

use App\Actions\Jetstream\DeleteUser;
use App\Http\Requests\{
    UserDestroyRequest,
    UserIndexRequest,
    UserPasswordRequest,
    UserStoreRequest,
    UserUpdateRequest,
};
use App\Models\User;
use App\Services\{
    CountryService,
    LanguageService,
    MediaService,
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
    public function index(UserIndexRequest $request)
    {
        $user = auth()->user();
        $scopes = [];

        $scopes['inRoles'] = $request->roles ?? null;

        return Inertia::render('User/Index', $this->getData([
            'can' => [
                'add' => $user->can('user.add'),
                'delete' => $user->can('user.delete'),
                'edit' => $user->can('user.edit'),
                'manageTrashed' => $user->isSuperAdministrator,
                'managePasswordResetEmail' => $user->can('managePasswordResetEmail', User::class),
            ],
            'pageNumber' => $request->page,
            'pageQueryParams' => array_filter($request->only('term', 'roles')),
            'records' => $this->userService->getRecords(
                $user,
                $request->term,
                $this->recordsPerPage,
                $scopes,
            ),
            'roleOptions' => $this->userService->getRoleOptions(),
            'title' => $this->getIndexTitle(),
            'i18n' => $this->translationIndexPage(),
        ]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $record = new User();
        $record->language_id = app(LanguageService::class)->getDefaultId();

        return Inertia::render('User/Create', $this->getData([
            'breadcrumbs' => [
                [
                    'title' => $this->getIndexTitle(),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                [
                    'title' => $this->getCreateTitle(),
                ],
            ],
            'record' => $record,
            'roleOptions' => $this->userService->getRoleOptions(),
            'supportedLanguageOptions' => app(LanguageService::class)->getSupportedLanguageOptions(),
            'title' => $this->getCreateTitle(),
            'i18n' => $this->translationCreateEditPage(),
            'instructions' => [
                'profilePicture' => MediaService::profilePictureInstructions(),
            ],
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
            'language_id' => $inputs['language_id'],
        ]);

        $user->savePassword($request->password);

        $user->saveDefaultMetas();

        $user->verifiyEmail();

        if ($request->hasFile('photo')) {
            $user->updateProfilePhoto($request->file('photo'));
        }

        if ($request->has('role')) {
            $user->assignRole($request->role);
        }

        $this->generateFlashMessage('The :resource was created!', [
            'resource' => __('User')
        ]);

        return redirect()->route($this->baseRouteName.'.index');
    }

    public function edit(User $user)
    {
        $canPublicPage = $user->hasPublicPage;

        $user->load(['roles' => function ($query) {
            $query->select(['id', 'name']);
            $query->limit(1);
        }]);

        $user->append(
            'isSuperAdministrator',
            'isTrashed',
            'optimizedProfilePhotoUrl',
            'profilePageUrl',
        );

        $user->roles->makeHidden('pivot');

        return Inertia::render('User/Edit', $this->getData([
            'breadcrumbs' => [
                [
                    'title' => $this->getIndexTitle(),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                [
                    'title' => $this->getEditTitle(),
                ],
            ],
            'can' => [
                'public_profile' => $canPublicPage,
                'update_password' => auth()->user()->can('updatePassword', $user),
            ],
            'record' => $user,
            'roleOptions' => $this->userService->getRoleOptions(),
            'supportedLanguageOptions' => app(LanguageService::class)->getSupportedLanguageOptions(),
            'countryOptions' => app(CountryService::class)->getCountryOptions(),
            'title' => $this->getEditTitle(),
            'i18n' => $this->translationCreateEditPage(),
            'instructions' => [
                'profilePicture' => MediaService::profilePictureInstructions(),
            ],
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
            $request->is_photo_deleted
            && $user->profile_photo_media_id != null
        ) {
            $user->deleteProfilePhoto();
        }

        $user->saveFromInputs($request->only([
            'first_name',
            'last_name',
            'email',
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

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('User')
        ]);

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
        } else {
            $this->userService->deleteResources($user->id);
        }

        $this->deleteUser->delete($user);

        $this->generateFlashMessage('The :resource was deleted!', [
            'resource' => __('User')
        ]);

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

    public function getTrashedRecords(UserIndexRequest $request) {
        $scopes = [];

        $scopes['inRoles'] = $request->roles ?? null;

        return $this->userService->getTrashedRecords(
            $request->term,
            $this->recordsPerPage,
            $scopes,
        );
    }

    private function translationIndexPage(): array
    {
        return [
            'users' => __('Users'),
            'deleted_users' => __('Deleted users'),
            'search' => __('Search'),
            'filter' => __('Filter'),
            'filter_by_role' => __('Filter by Role'),
            'create_new' => __('Create new'),
            'name' => __('Name'),
            'email' => __('Email'),
            'role' => __('Role'),
            'actions' => __('Actions'),
            'delete_user' => __('Delete user'),
            'delete_user_action' => __('What should be done with content owned by the user?'),
            'delete_all_content' => __('Delete all content'),
            'attribute_all_content_to' => __('Attribute all content to'),
            'select_user' => __('Select a user'),
            'cancel' => __('Cancel'),
            'delete' => __('Delete'),
            'are_you_sure' => __('Are you sure?'),
            'delete_confirmation' => __('Once you hit "Confirm Deletion", the user will be permanently removed.'),
            'confirm_deletion' => __('Confirm deletion'),
            'suspend_user_confirmation' => __('The user will be suspended.'),
            'unsuspend_user_confirmation' => __('The user will be unsuspended.'),
            'user_password_reset' => __('User Password Reset'),
            'send_password_reset_link' => __('Send Password Reset Link'),
            'send' => __('Send'),
        ];
    }

    private function translationCreateEditPage(): array
    {
        return [
            'profile_picture' => __('Profile picture'),
            'Choose_a_picture' => __('Choose a picture'),
            'first_name' => __('First name'),
            'last_name' => __('Last name'),
            'email' => __('Email'),
            'role' => __('Role'),
            'select_a_role' => __('Select a Role'),
            'language' => __('Language'),
            'password' => __('Password'),
            'password_confirmation' => __('Password confirmation'),
            'cancel' => __('Cancel'),
            'create' => __('Create'),
            'update' => __('Update'),
            'profile' => __('Profile'),
            'profile_information' => __('Profile information'),
            'open_public_profile' => __('Open public profile'),
            'user_password_reset' => __('User Password Reset'),
            'send_password_reset_link' => __('Send Password Reset Link'),
            'send' => __('Send'),
        ];
    }
}
