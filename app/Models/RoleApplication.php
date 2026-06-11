<?php

namespace App\Models;

use App\Enums\RoleApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleApplication extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'password',
        'first_name',
        'last_name',
        'requested_role',
        'city_id',
        'status',
        'logo_media_id',
        'cover_media_id',
        'description',
        'excerpt',
        'branding',
        'reviewed_by',
        'reviewed_at',
        'reject_reason',
        'replaced_user_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'branding' => 'array',
        'reviewed_at' => 'datetime',
        'status' => RoleApplicationStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function logoMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function replacedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replaced_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === RoleApplicationStatus::PENDING;
    }

    public function scopePending($query)
    {
        return $query->where('status', RoleApplicationStatus::PENDING->value);
    }

    public function getApplicantFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
