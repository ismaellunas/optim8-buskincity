<?php

namespace App\Models;

use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use JoelButcher\Socialstream\HasConnectedAccounts;
use JoelButcher\Socialstream\SetsProfilePhotoFromUrl;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto {
        getProfilePhotoUrlAttribute as getPhotoUrl;
    }
    use HasConnectedAccounts;
    use Notifiable;
    use SetsProfilePhotoFromUrl;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    public function media()
    {
        return $this->hasMany(Media::class, 'author_id');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($query) use ($term) {
            $query
                ->where('name', 'ILIKE', '%'.$term.'%')
                ->orWhere('email', 'ILIKE', '%'.$term.'%');
        });
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        if (filter_var($this->profile_photo_path, FILTER_VALIDATE_URL)) {
            return $this->profile_photo_path;
        }

        return $this->getPhotoUrl();
    }

    public function getIsSuperAdministratorAttribute(): bool
    {
        return $this->hasRole(config('permission.super_admin_role'));
    }

    public function saveFromInputs(array $inputs)
    {
        $this->name = $inputs['name'];
        $this->email = $inputs['email'];
        $this->save();
    }

    public function savePassword(string $password)
    {
        $this->password = UserService::hashPassword($password);
        $this->save();
    }
}
