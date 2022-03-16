<?php

namespace App\Models;

use App\Entities\CloudinaryStorage;
use App\Traits\HasMetas;
use App\Services\{
    MediaService,
    UserService
};
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use JoelButcher\Socialstream\HasConnectedAccounts;
use JoelButcher\Socialstream\SetsProfilePhotoFromUrl;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasConnectedAccounts;
    use Notifiable;
    use SetsProfilePhotoFromUrl;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use HasMetas;

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
        'country_code',
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
        'origin_language_code'
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
                ->where('first_name', 'ILIKE', '%'.$term.'%')
                ->where('last_name', 'ILIKE', '%'.$term.'%')
                ->orWhere('email', 'ILIKE', '%'.$term.'%');
        });
    }

    public function scopeInRoles($query, array $roleIds)
    {
        return $query->whereHas('roles', function ($query) use ($roleIds) {
            $query->whereIn('id', $roleIds);
        });
    }

    public function getFullNameAttribute(): string
    {
        return trim(ucfirst($this->first_name) . ' ' . ucfirst($this->last_name));
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        $url = null;
        if ($this->profilePhoto) {
            $url = $this->profilePhoto->file_url;
        }

        return $url;
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
        return $this->hasRole('Administrator');
    }

    public function getOriginLanguageCodeAttribute(): ?string
    {
        return $this->originLanguage
            ? $this->originLanguage->code
            : null;
    }

    public function getRoleNameAttribute(): string
    {
        return $this->getRoleNames()->first();
    }

    public function saveFromInputs(array $inputs)
    {
        $this->first_name = $inputs['first_name'];
        $this->last_name = $inputs['last_name'];
        $this->email = $inputs['email'];
        $this->country_code = $inputs['country_code'];
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

    public function updateProfilePhoto(UploadedFile $photo)
    {
        $user = Auth::user();
        $media = app(MediaService::class)->uploadProfile(
            $photo,
            new CloudinaryStorage(),
            $user,
            "profiles",
        );

        app(MediaService::class)->setMedially($user, [
            $media->id
        ]);

        if ($this->profile_photo_media_id) {
            $this->deleteProfilePhoto();
        }

        $this->profile_photo_media_id = $media->id;
        $this->save();
    }

    public function deleteProfilePhoto()
    {
        $media = Media::find($this->profile_photo_media_id);

        if ($media) {
            app(MediaService::class)->destroy($media, new CloudinaryStorage());
        }
    }
}
