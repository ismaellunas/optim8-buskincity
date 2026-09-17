<?php

namespace App\Services;

use App\Entities\CloudinaryStorage;
use App\Enums\RoleApplicationStatus;
use App\Mail\RoleApplicationApproved;
use App\Models\City;
use App\Models\GlobalOption;
use App\Models\RoleApplication;
use App\Models\User;
use App\Models\Media;
use App\Models\UserScope;
use Modules\Space\Entities\Space;
use App\Rules\Password;
use App\Rules\ProtectedAdminEmail;
use App\Services\CountryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image as InterventionImage;
use Modules\Space\Services\SpaceService;

class RoleApplicationService
{
    public function __construct(
        private MediaService $mediaService,
        private UserRoleService $userRoleService,
        private SpaceService $spaceService,
    ) {}

    /**
     * @return array<int, string>
     */
    public function allowedRoles(): array
    {
        return [
            config('permission.role_names.city_admin'),
            config('permission.role_names.special_events_admin'),
        ];
    }

    public function getRecords(
        ?string $term = null,
        ?string $status = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return RoleApplication::query()
            ->with([
                'city:id,name,country_code',
                'countrySpace:id,name,country_code',
                'reviewer:id,first_name,last_name,email',
            ])
            ->when($term, function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('email', 'ILIKE', "%{$term}%")
                        ->orWhere('first_name', 'ILIKE', "%{$term}%")
                        ->orWhere('last_name', 'ILIKE', "%{$term}%");
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(array $data, ?UploadedFile $logo = null, ?UploadedFile $cover = null): RoleApplication
    {
        $user = auth()->user();
        $linkedUserId = ($user && strcasecmp($user->email, $data['email']) === 0)
            ? $user->id
            : null;

        $logoMediaId = $logo ? $this->uploadBrandingImage($logo)->id : null;
        $coverMediaId = $cover ? $this->uploadBrandingImage($cover)->id : null;

        $attributes = [
            'user_id' => $linkedUserId,
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'requested_role' => $data['requested_role'],
            'city_id' => (int) $data['city_id'],
            'country_space_id' => $this->requiresCountrySpaceOnSubmit($data['requested_role'])
                ? (int) $data['country_space_id']
                : null,
            'status' => RoleApplicationStatus::PENDING,
            'logo_media_id' => $logoMediaId,
            'cover_media_id' => $coverMediaId,
            'description' => $data['description'] ?? null,
            'excerpt' => $data['excerpt'] ?? null,
        ];

        if ($this->requiresPasswordOnSubmit($data['requested_role'])) {
            $attributes['password'] = UserService::hashPassword($data['password']);
        }

        return RoleApplication::create($attributes);
    }

    /**
     * @return array<string, mixed>
     */
    public function approvalPreview(RoleApplication $application): array
    {
        $this->assertPending($application);

        $existingCityAdmin = null;
        $hasOccupyingScope = false;
        $cityId = (int) $application->city_id;

        if ($application->requested_role === config('permission.role_names.city_admin')) {
            $hasOccupyingScope = $this->findCityAdminScopeForCity($cityId) !== null;
            $existingCityAdmin = $this->findLivingCityAdminForCity($cityId);
        }

        return [
            'application_id' => $application->id,
            'requires_replace_confirmation' => $existingCityAdmin !== null,
            'existing_city_admin' => $existingCityAdmin ? [
                'id' => $existingCityAdmin->id,
                'name' => $existingCityAdmin->fullName,
                'email' => $existingCityAdmin->email,
            ] : null,
            'city' => $application->city?->only(['id', 'name', 'country_code']),
            'city_available' => $this->cityCatalogExists($cityId),
            'city_space_missing' => $hasOccupyingScope
                && $this->cityCatalogExists($cityId)
                && ! $this->cityLocationSpaceExists($cityId),
            'country' => $application->countrySpace?->only(['id', 'name', 'country_code']),
            'applicant' => [
                'name' => $application->applicant_full_name,
                'email' => $application->email,
            ],
            'requested_role' => $application->requested_role,
        ];
    }

    public function approve(RoleApplication $application, User $reviewer, bool $confirmReplace = false): RoleApplication
    {
        $this->assertPending($application);

        $preview = $this->approvalPreview($application);

        if ($preview['requires_replace_confirmation'] && ! $confirmReplace) {
            throw ValidationException::withMessages([
                'confirm_replace' => [__('You must confirm replacing the existing City Administrator for this city.')],
            ]);
        }

        if (! $preview['city_available']) {
            throw ValidationException::withMessages([
                'city_id' => [__('This city is no longer available. The application cannot be approved.')],
            ]);
        }

        return DB::transaction(function () use ($application, $reviewer, $confirmReplace) {
            $application = RoleApplication::whereKey($application->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertPending($application);

            $this->assertProtectedEmail($application->email);

            if (! $this->cityCatalogExists((int) $application->city_id)) {
                throw ValidationException::withMessages([
                    'city_id' => [__('This city is no longer available. The application cannot be approved.')],
                ]);
            }

            $replacedUser = $this->replaceExistingCityAdmin(
                (int) $application->city_id,
                $confirmReplace
            );

            $user = $this->resolveApplicantUser($application);
            $user->verifiyEmail();

            $this->assignRoleAndScope($user, $application);

            $this->spaceService->provisionCitySpaceForApplication(
                $user,
                (int) $application->city_id,
                $this->brandingPayload($application),
                $application->country_space_id
            );

            $shouldNotifyApprovedLogin = filled($application->password);

            $application->update([
                'user_id' => $user->id,
                'status' => RoleApplicationStatus::APPROVED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'reject_reason' => null,
                'replaced_user_id' => $replacedUser?->id,
                'password' => null,
            ]);

            if ($shouldNotifyApprovedLogin) {
                $this->sendApprovedNotification($user, $application->requested_role);
            }

            return $application->fresh(['city', 'reviewer', 'replacedUser']);
        });
    }

    public function reject(RoleApplication $application, User $reviewer, string $reason): RoleApplication
    {
        $this->assertPending($application);

        $application->update([
            'status' => RoleApplicationStatus::REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'reject_reason' => $reason,
        ]);

        return $application->fresh(['city', 'reviewer']);
    }

    public function findCityAdminForCity(int $cityId): ?User
    {
        return $this->findLivingCityAdminForCity($cityId);
    }

    private function findCityAdminScopeForCity(int $cityId, bool $lock = false): ?UserScope
    {
        $query = UserScope::query()
            ->where('role', config('permission.role_names.city_admin'))
            ->where('scope_type', 'city')
            ->where('scope_id', $cityId);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function findLivingCityAdminForCity(int $cityId): ?User
    {
        $scope = $this->findCityAdminScopeForCity($cityId);

        if (! $scope) {
            return null;
        }

        $user = User::withTrashed()->find($scope->user_id);

        if (! $user || $user->trashed()) {
            return null;
        }

        return $user;
    }

    /**
     * Release the occupying city_administrator scope for this city so the unique
     * index user_scope_one_city_admin_per_city is free for the incoming admin.
     *
     * Living occupants require confirm_replace. Soft-deleted / missing users
     * leave orphaned user_scope rows (FK cascade only runs on hard delete) and
     * are cleared without confirmation.
     */
    private function replaceExistingCityAdmin(int $cityId, bool $confirmed): ?User
    {
        $role = config('permission.role_names.city_admin');

        $existing = $this->findCityAdminScopeForCity($cityId, lock: true);

        if (! $existing) {
            return null;
        }

        $oldUser = User::withTrashed()->find($existing->user_id);
        $isLivingOccupant = $oldUser && ! $oldUser->trashed();

        if ($isLivingOccupant && ! $confirmed) {
            throw ValidationException::withMessages([
                'confirm_replace' => [__('Replacing the existing City Administrator must be confirmed.')],
            ]);
        }

        if ($oldUser?->isCityAdministrator()) {
            $remainingCityIds = collect($oldUser->scopeIdsFor($role, 'city'))
                ->merge($oldUser->adminCities()->pluck('cities.id'))
                ->unique()
                ->map(fn ($id) => (int) $id)
                ->reject(fn ($id) => $id === $cityId)
                ->values()
                ->all();

            $oldUser->syncAdminCities($remainingCityIds);

            if ($remainingCityIds === [] && $isLivingOccupant) {
                $this->userRoleService->syncSingleRole($oldUser, null);
            }
        }

        UserScope::query()
            ->where('role', $role)
            ->where('scope_type', 'city')
            ->where('scope_id', $cityId)
            ->delete();

        if ($oldUser) {
            DB::table('city_user')
                ->where('user_id', $oldUser->id)
                ->where('city_id', $cityId)
                ->delete();
        }

        return $oldUser;
    }

    private function cityCatalogExists(int $cityId): bool
    {
        return $cityId > 0 && City::query()->whereKey($cityId)->exists();
    }

    private function cityLocationSpaceExists(int $cityId): bool
    {
        $cityTypeId = GlobalOption::where('name', 'City')->value('id');

        if (! $cityTypeId) {
            return false;
        }

        return Space::query()
            ->where('type_id', $cityTypeId)
            ->where('city_id', $cityId)
            ->exists();
    }

    private function resolveApplicantUser(RoleApplication $application): User
    {
        $user = $application->user_id
            ? User::find($application->user_id)
            : User::firstWhere('email', $application->email);

        if (! $user) {
            $user = User::factory()->create([
                'email' => $application->email,
                'first_name' => $application->first_name,
                'last_name' => $application->last_name,
                'password' => $application->password
                    ?? UserService::hashPassword(str()->random(32)),
                'language_id' => app(LanguageService::class)->getDefaultId(),
            ]);
        } else {
            $user->first_name = $application->first_name;
            $user->last_name = $application->last_name;

            if ($application->password) {
                $user->password = $application->password;
            }

            $user->save();
        }

        return $user;
    }

    private function assignRoleAndScope(User $user, RoleApplication $application): void
    {
        $role = $application->requested_role;
        $cityId = (int) $application->city_id;

        $this->userRoleService->syncSingleRole($user, $role);

        if ($role === config('permission.role_names.city_admin')) {
            // Occupant must already have been released; sweep any leftover row so
            // user_scope_one_city_admin_per_city cannot 500 on insert.
            UserScope::query()
                ->where('role', $role)
                ->where('scope_type', 'city')
                ->where('scope_id', $cityId)
                ->where('user_id', '!=', $user->id)
                ->delete();

            $user->syncAdminCities([$cityId]);

            return;
        }

        $existingCityIds = collect($user->scopeIdsFor($role, 'city'))
            ->map(fn ($id) => (int) $id)
            ->push($cityId)
            ->unique()
            ->values()
            ->all();

        $user->syncScopeCities($role, $existingCityIds);
    }

    /**
     * @return array{logo_media_id?: int|null, cover_media_id?: int|null, description?: string|null, excerpt?: string|null}
     */
    private function brandingPayload(RoleApplication $application): array
    {
        return array_filter([
            'logo_media_id' => $application->logo_media_id,
            'cover_media_id' => $application->cover_media_id,
            'description' => $application->description,
            'excerpt' => $application->excerpt,
        ], fn ($value) => ! is_null($value) && $value !== '');
    }

    private function uploadBrandingImage(UploadedFile $file): Media
    {
        $allowed = config('constants.extensions.image');

        Validator::make(
            ['file' => $file],
            [
                'file' => [
                    'required',
                    'file',
                    'mimes:'.implode(',', $allowed),
                    'max:'.SettingService::maxFileSize(),
                ],
            ]
        )->validate();

        $stripped = $this->reencodeStrippingExif($file);

        try {
            return $this->mediaService->upload(
                $stripped,
                $this->mediaService->sanitizeFileName($file->getClientOriginalName()),
                new CloudinaryStorage()
            );
        } finally {
            @unlink($stripped->getPathname());
        }
    }

    /**
     * Re-encode the image via Intervention, discarding all EXIF/metadata.
     * Returns a temp UploadedFile; caller is responsible for unlinking it.
     */
    private function reencodeStrippingExif(UploadedFile $file): UploadedFile
    {
        $ext = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $encodeFormat = $ext === 'jpg' ? 'jpeg' : $ext;

        $binary = (string) InterventionImage::make($file->getPathname())->encode($encodeFormat, 90);

        $tmp = tempnam(sys_get_temp_dir(), 'branding_');
        file_put_contents($tmp, $binary);

        return new UploadedFile($tmp, $file->getClientOriginalName(), $file->getClientMimeType(), null, true);
    }

    private function assertPending(RoleApplication $application): void
    {
        if (! $application->isPending()) {
            throw ValidationException::withMessages([
                'status' => [__('This application has already been reviewed.')],
            ]);
        }
    }

    private function assertProtectedEmail(string $email): void
    {
        $validator = Validator::make(
            ['email' => $email],
            ['email' => ['required', 'email', new ProtectedAdminEmail()]]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function submissionRules(?string $requestedRole = null): array
    {
        $role = $requestedRole ?? request()->input('requested_role');

        $rules = [
            'email' => ['required', 'email', 'max:255', new ProtectedAdminEmail()],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'requested_role' => ['required', Rule::in($this->allowedRoles())],
            'description' => ['nullable', 'string', 'max:5000'],
            'excerpt' => ['nullable', 'string', 'max:500'],
        ];

        if ($this->requiresCountrySpaceOnSubmit($role)) {
            $rules['country_space_id'] = [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->spaceService->findCountrySpace((int) $value)) {
                        $fail(__('The selected country is invalid.'));
                    }
                },
            ];
        }

        $rules['city_id'] = [
            'required',
            'integer',
            'exists:cities,id',
            function (string $attribute, mixed $value, \Closure $fail) use ($role): void {
                if (! $this->requiresCountrySpaceOnSubmit($role)) {
                    return;
                }

                $countrySpaceId = request()->integer('country_space_id');

                if (! $countrySpaceId) {
                    return;
                }

                $countrySpace = $this->spaceService->findCountrySpace($countrySpaceId);
                $city = City::find($value);

                if (! $countrySpace || ! $city) {
                    return;
                }

                $countryService = app(CountryService::class);
                $cityAlpha2 = $countryService->toAlpha2($city->country_code);
                $spaceAlpha2 = $countryService->toAlpha2($countrySpace->country_code);

                if ($cityAlpha2 !== $spaceAlpha2) {
                    $fail(__('The selected city does not belong to the selected country.'));
                }
            },
        ];

        if ($this->requiresPasswordOnSubmit($role)) {
            $rules['password'] = ['required', 'string', new Password()];
            $rules['password_confirmation'] = ['required', 'same:password'];
        }

        return $rules;
    }

    public function requiresCountrySpaceOnSubmit(?string $role): bool
    {
        return $role === config('permission.role_names.city_admin');
    }

    /**
     * @return array<int, array{id: int, name: string, country_code: string|null}>
     */
    public function countrySpaceOptionsForApplication(): array
    {
        return $this->spaceService->getApplicationCountrySpaceOptions();
    }

    public function requiresPasswordOnSubmit(?string $role): bool
    {
        return $role === config('permission.role_names.city_admin');
    }

    private function sendApprovedNotification(User $user, string $requestedRole): void
    {
        Mail::to($user)->send(new RoleApplicationApproved(
            $user,
            $this->roleLabel($requestedRole),
            route('admin.login'),
        ));
    }

    private function roleLabel(string $role): string
    {
        return match ($role) {
            config('permission.role_names.special_events_admin') => __('Special Events Administrator'),
            default => __('City Administrator'),
        };
    }

}
