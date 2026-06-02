<?php

namespace App\Models;

use App\Entities\CloudinaryStorage;
use App\Notifications\{
    ResetPassword,
    VerifyEmail,
};
use App\Traits\HasMetas;
use App\Services\{
    IPService,
    MediaService,
    SettingService,
    TranslationService,
    UserService,
};
use Carbon\Carbon;
use Lunar\Base\Traits\LunarUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JoelButcher\Socialstream\HasConnectedAccounts;
use JoelButcher\Socialstream\SetsProfilePhotoFromUrl;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasMacros;

class User extends Authenticatable implements MustVerifyEmail
{
    use LunarUser;
    use HasApiTokens;
    use HasConnectedAccounts;
    use HasFactory;
    use HasMetas;
    use HasRoles;
    use Notifiable;
    use SetsProfilePhotoFromUrl;
    use SoftDeletes;
    use TwoFactorAuthenticatable;
    use HasMacros;

    protected $metaRelation = 'metas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'profile_photo_media_id',
        'language_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
        'full_name',
    ];

    /* Relationship: */
    public function pages()
    {
        return $this->hasMany(Media::class, 'author_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function profilePhoto()
    {
        return $this->belongsTo(Media::class, 'profile_photo_media_id');
    }

    public function scopeEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    public function metas()
    {
        return $this->hasMany(UserMeta::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'medially');
    }

    public function originLanguage()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_code', 'alpha2');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($query) use ($term) {
            $query
                ->where(DB::raw("CONCAT(first_name,' ',last_name)"), 'ILIKE', '%'.$term.'%')
                ->orWhere('email', 'ILIKE', '%'.$term.'%');
        });
    }

    public function scopeInRoles($query, array $roleIds)
    {
        return $query->whereHas(
            'roles',
            function (Builder $query) use ($roleIds) {
                $query->whereIn('id', $roleIds);
            }
        );
    }

    public function scopeInRoleNames($query, array $roleNames)
    {
        return $query->whereHas(
            'roles',
            function (Builder $query) use ($roleNames) {
                $query->whereIn('name', $roleNames);
            }
        );
    }

    public function scopeNotInRoleNames($query, array $roleNames)
    {
        return $query->whereDoesntHave(
            'roles',
            function (Builder $query) use ($roleNames) {
                $query->whereIn('name', $roleNames);
            }
        );
    }

    public function scopeHasPermissionNames($query, array $permissionNames = [])
    {
        return $query->whereHas('roles', function ($query) use ($permissionNames) {
            $query->whereHas('permissions', function ($query) use ($permissionNames) {
                $query->whereIn('name', $permissionNames);
            });
        });
    }

    public function scopeEmailVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeAvailable($query)
    {
        return $query
            ->emailVerified()
            ->where('is_suspended', false);
    }

    public function scopeBackend($query)
    {
        return $query->hasPermissionNames(['system.dashboard']);
    }

    public function getFullNameAttribute(): string
    {
        return trim(ucfirst($this->first_name ?? '') . ' ' . ucfirst($this->last_name ?? ''));
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        $url = null;
        if ($this->profilePhoto) {
            $url = $this->profilePhoto->file_url;
        }

        return $url;
    }

    public function getOptimizedProfilePhotoUrlAttribute(): ?string
    {
        if ($this->profilePhoto) {
            $dimensions = config('constants.dimensions.profile_picture');

            return $this->profilePhoto->getOptimizedImageUrl(
                $dimensions['width'],
                $dimensions['height'],
            );
        }

        return null;
    }

    public function getRegisteredAtAttribute()
    {
        return Carbon::parse($this->created_at)->format("Y-m-d H:i:s");
    }

    public function getIsSuperAdministratorAttribute(): bool
    {
        return $this->hasRole(config('permission.super_admin_role'));
    }

    public function getIsAdministratorAttribute(): bool
    {
        return (
            $this->hasRole(config('permission.role_names.admin'))
            || $this->isSuperAdministrator
        );
    }

    public function getOriginLanguageCodeAttribute(): ?string
    {
        return $this->originLanguage
            ? $this->originLanguage->code
            : null;
    }

    public function getRoleNameAttribute(): ?string
    {
        return $this->getRoleNames()->first();
    }

    public function getRoleIdAttribute(): ?int
    {
        return $this->roles->pluck('id')->first();
    }

    public function getLanguageCodeAttribute(): string
    {
        $defaultLocale = app(TranslationService::class)->getDefaultLocale();

        return $this->originLanguageCode ?? $defaultLocale;
    }

    public function saveFromInputs(array $inputs)
    {
        $this->first_name = $inputs['first_name'];
        $this->last_name = $inputs['last_name'];
        $this->email = $inputs['email'];
        $this->language_id = $inputs['language_id'];
        $this->save();
    }

    public function savePassword(string $password)
    {
        $this->password = UserService::hashPassword($password);
        $this->save();
    }

    public function verifiyEmail(mixed $dateTime = null)
    {
        $this->email_verified_at = $dateTime ?? now();
        $this->save();
    }

    public function suspend()
    {
        $this->is_suspended = true;
        $this->save();
    }

    public function unsuspend()
    {
        $this->is_suspended = false;
        $this->save();
    }

    public function generateProfilePhotoFileName()
    {
        return htmlspecialchars(
            $this->first_name.'-'.$this->last_name.'-'.Str::random(10)
        );
    }

    public function replaceProfilePhoto(int $mediaId)
    {
        if ($this->profile_photo_media_id) {
            $this->deleteProfilePhoto();
        }

        $this->profile_photo_media_id = $mediaId;
        $this->save();
    }

    public function updateProfilePhoto(UploadedFile $photo): void
    {
        $fileName = $this->generateProfilePhotoFileName();

        $media = app(MediaService::class)->uploadProfile(
            $photo,
            $fileName,
            new CloudinaryStorage()
        );

        $this->replaceProfilePhoto($media->id);

        app(MediaService::class)->setMedially($this, [
            $media->id
        ]);
    }

    public function deleteProfilePhoto(): void
    {
        $media = Media::where('id', $this->profile_photo_media_id)->first();

        if ($media) {
            app(MediaService::class)->destroy($media, new CloudinaryStorage());
        }
    }

    public function getProfilePageUrlAttribute(?array $parameters = []): string
    {
        $className = $this->getPublicPageProfileUrlClassName();

        return (new $className($this, $parameters ?? []))->url();
    }

    private function getPublicPageProfileUrlClassName(): string
    {
        $profileSlugType = app(SettingService::class)->getPublicPageProfileSlugType();

        $className = "App\\Entities\\PublicPageProfile\\".Str::studly($profileSlugType)."Url";

        if (
            class_exists($className)
            && method_exists($className, 'url')
        ) {
            return $className;
        }

        return "App\\Entities\\PublicPageProfile\\DefaultUrl";
    }

    public function getQrCodeLogoNameAttribute(): string
    {
        return 'qrcode-'.$this->unique_key.'-'.Str::of($this->fullName)->slug();
    }

    public function sendPasswordResetNotification($token)
    {
        app()->setLocale($this->languageCode);

        $this->notify(new ResetPassword($token));
    }

    public function sendEmailVerificationNotification()
    {
        $verifyEmail = (new VerifyEmail())->locale($this->languageCode);

        if ($this->can('system.dashboard')) {
            $verifyEmail = $verifyEmail->admin();
        }

        $this->notify($verifyEmail);
    }

    public function getIsConnectedAccountAttribute(): bool
    {
        return is_null($this->password) && ($this->connectedAccounts->count() > 0);
    }

    public function getHasPublicPageAttribute(): bool
    {
        $hasPermission = $this->roles->contains(function ($role) {
            return $role->hasPermissionTo('public_page.profile');
        });

        return $hasPermission && $this->isAvailable;
    }

    public function saveDefaultMetas(array $metas = [])
    {
        $metas = array_merge($metas, [
            'country' => app(IPService::class)->getCountryCode(),
        ]);

        foreach ($metas as $key => $meta) {
            $this->setMeta($key, $meta);
        }

        $this->saveMetas();
    }

    public function getIsAvailableAttribute(): bool
    {
        return !$this->is_suspended;
    }

    public function getIsTrashedAttribute(): bool
    {
        return $this->trashed();
    }

    public function getHasPasswordAttribute(): bool
    {
        return !! $this->password;
    }

    public function getOptimizedProfilePhotoOrDefaultUrlAttribute(): string
    {
        return $this->optimizedProfilePhotoUrl
            ?? config('constants.profile_photo_path');
    }

    /**
     * Cities this user administers
     */
    public function adminCities(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(City::class, 'city_user');
    }

    /**
     * Generalized, role-aware authorization scopes (OQ10).
     */
    public function userScopes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserScope::class);
    }

    /**
     * Scope ids this user holds for a given role + scope type
     * (e.g. the city ids a city_administrator manages).
     *
     * @return array<int, int>
     */
    public function scopeIdsFor(string $role, string $scopeType): array
    {
        return $this->userScopes()
            ->where('role', $role)
            ->where('scope_type', $scopeType)
            ->pluck('scope_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Whether this user is scoped to a given entity (optionally for a role).
     */
    public function inScope(string $scopeType, int $scopeId, ?string $role = null): bool
    {
        return $this->userScopes()
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->when($role, fn ($query) => $query->where('role', $role))
            ->exists();
    }

    /**
     * Set the cities this user administers, writing BOTH the legacy `city_user`
     * pivot (kept for dual-read during transition) and the generalized
     * `user_scope` rows (canonical going forward).
     *
     * @param  array<int, int|string>  $cityIds
     */
    public function syncAdminCities(array $cityIds): void
    {
        $cityIds = array_values(array_unique(array_map('intval', $cityIds)));

        // Legacy dual-write: keep `city_user` in sync during the transition.
        $this->adminCities()->sync($cityIds);

        $this->syncScopeCities(config('permission.role_names.city_admin'), $cityIds);
    }

    /**
     * Generalized, role-aware city-scope writer (OQ10). Writes ONLY the
     * canonical `user_scope` rows for the given role; callers that also need
     * the legacy `city_user` pivot (city_administrator) should use
     * syncAdminCities() which dual-writes.
     *
     * @param  array<int, int|string>  $cityIds
     */
    public function syncScopeCities(string $role, array $cityIds): void
    {
        $cityIds = array_values(array_unique(array_map('intval', $cityIds)));

        $this->userScopes()
            ->where('role', $role)
            ->where('scope_type', 'city')
            ->whereNotIn('scope_id', $cityIds ?: [0])
            ->delete();

        foreach ($cityIds as $cityId) {
            $this->userScopes()->updateOrCreate(
                ['role' => $role, 'scope_type' => 'city', 'scope_id' => $cityId],
                []
            );
        }
    }

    /**
     * Cities held for a given scope role, resolved from `user_scope`.
     */
    public function scopedCities(string $role): \Illuminate\Support\Collection
    {
        $ids = $this->scopeIdsFor($role, 'city');

        if (empty($ids)) {
            return collect();
        }

        return City::whereIn('id', $ids)->get(['id', 'name', 'country_code']);
    }

    /**
     * Cities assigned to whichever city-scoped role this user currently holds.
     * Used by the admin "assigned cities" UI for both City and Special Events admins.
     */
    public function assignedScopeCities(): \Illuminate\Support\Collection
    {
        if ($this->isSpecialEventsAdmin()) {
            return $this->scopedCities(config('permission.role_names.special_events_admin'));
        }

        if ($this->isCityAdministrator()) {
            return $this->adminCities()->get(['cities.id', 'name', 'country_code']);
        }

        return collect();
    }

    /**
     * Check if user is a City Administrator for a specific city
     */
    public function isCityAdmin(int $cityId): bool
    {
        return $this->hasRole(config('permission.role_names.city_admin'))
            && $this->adminCities()->where('cities.id', $cityId)->exists();
    }

    /**
     * Check if user is a City Administrator (any city)
     */
    public function isCityAdministrator(): bool
    {
        return $this->hasRole(config('permission.role_names.city_admin'));
    }

    /**
     * Check if user is a Special Events Administrator (any city).
     */
    public function isSpecialEventsAdmin(): bool
    {
        return $this->hasRole(config('permission.role_names.special_events_admin'));
    }
}
