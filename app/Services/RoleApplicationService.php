<?php

namespace App\Services;

use App\Entities\CloudinaryStorage;
use App\Enums\RoleApplicationStatus;
use App\Models\RoleApplication;
use App\Models\User;
use App\Models\Media;
use App\Models\UserScope;
use App\Rules\ProtectedAdminEmail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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
            ->with(['city:id,name,country_code', 'reviewer:id,first_name,last_name,email'])
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
        $validator = Validator::make($data, $this->submissionRules());
        $validator->validate();

        $user = auth()->user();
        $linkedUserId = ($user && strcasecmp($user->email, $data['email']) === 0)
            ? $user->id
            : null;

        $logoMediaId = $logo ? $this->uploadBrandingImage($logo)->id : null;
        $coverMediaId = $cover ? $this->uploadBrandingImage($cover)->id : null;

        return RoleApplication::create([
            'user_id' => $linkedUserId,
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'requested_role' => $data['requested_role'],
            'city_id' => (int) $data['city_id'],
            'status' => RoleApplicationStatus::PENDING,
            'logo_media_id' => $logoMediaId,
            'cover_media_id' => $coverMediaId,
            'description' => $data['description'] ?? null,
            'excerpt' => $data['excerpt'] ?? null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function approvalPreview(RoleApplication $application): array
    {
        $this->assertPending($application);

        $existingCityAdmin = null;

        if ($application->requested_role === config('permission.role_names.city_admin')) {
            $existingCityAdmin = $this->findCityAdminForCity((int) $application->city_id);
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

        return DB::transaction(function () use ($application, $reviewer, $preview, $confirmReplace) {
            $application = RoleApplication::whereKey($application->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertPending($application);

            $this->assertProtectedEmail($application->email);

            $replacedUser = null;

            if ($preview['requires_replace_confirmation']) {
                $replacedUser = $this->replaceExistingCityAdmin(
                    (int) $application->city_id,
                    $confirmReplace
                );
            }

            $user = $this->resolveApplicantUser($application);
            $user->verifiyEmail();

            $this->assignRoleAndScope($user, $application);

            $this->spaceService->provisionCitySpaceForApplication(
                $user,
                (int) $application->city_id,
                $this->brandingPayload($application)
            );

            $application->update([
                'user_id' => $user->id,
                'status' => RoleApplicationStatus::APPROVED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'reject_reason' => null,
                'replaced_user_id' => $replacedUser?->id,
            ]);

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
        $scope = UserScope::query()
            ->where('role', config('permission.role_names.city_admin'))
            ->where('scope_type', 'city')
            ->where('scope_id', $cityId)
            ->first();

        return $scope ? User::find($scope->user_id) : null;
    }

    private function replaceExistingCityAdmin(int $cityId, bool $confirmed): ?User
    {
        $existing = UserScope::query()
            ->where('role', config('permission.role_names.city_admin'))
            ->where('scope_type', 'city')
            ->where('scope_id', $cityId)
            ->lockForUpdate()
            ->first();

        if (! $existing) {
            return null;
        }

        if (! $confirmed) {
            throw ValidationException::withMessages([
                'confirm_replace' => [__('Replacing the existing City Administrator must be confirmed.')],
            ]);
        }

        $oldUser = User::find($existing->user_id);

        if (! $oldUser) {
            $existing->delete();

            return null;
        }

        $remainingCityIds = collect($oldUser->scopeIdsFor(
            config('permission.role_names.city_admin'),
            'city'
        ))
            ->merge($oldUser->adminCities()->pluck('cities.id'))
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === $cityId)
            ->values()
            ->all();

        if ($oldUser->isCityAdministrator()) {
            $oldUser->syncAdminCities($remainingCityIds);

            if ($remainingCityIds === []) {
                $this->userRoleService->syncSingleRole($oldUser, null);
            }
        } else {
            UserScope::query()
                ->where('user_id', $oldUser->id)
                ->where('role', config('permission.role_names.city_admin'))
                ->where('scope_type', 'city')
                ->where('scope_id', $cityId)
                ->delete();
        }

        return $oldUser;
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
                'password' => UserService::hashPassword(str()->random(32)),
                'language_id' => app(LanguageService::class)->getDefaultId(),
            ]);
        } else {
            $user->first_name = $application->first_name;
            $user->last_name = $application->last_name;
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
    public function submissionRules(): array
    {
        $maxKb = SettingService::maxFileSize();

        return [
            'email' => ['required', 'email', 'max:255', new ProtectedAdminEmail()],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'requested_role' => ['required', Rule::in($this->allowedRoles())],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'excerpt' => ['nullable', 'string', 'max:500'],
        ];
    }

}
