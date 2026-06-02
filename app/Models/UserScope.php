<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Generalized, role-aware authorization scope (OQ10).
 *
 * Replaces the City-Admin-only `city_user` pivot with a model that can scope any
 * role to any entity: e.g. (role=city_administrator, scope_type=city, scope_id=X)
 * or (role=special_events_admin, scope_type=city, scope_id=X). During the
 * transition `city_user` is still written and read (dual-read/dual-write); it is
 * NOT dropped.
 */
class UserScope extends Model
{
    protected $table = 'user_scope';

    protected $fillable = [
        'user_id',
        'role',
        'scope_type',
        'scope_id',
    ];

    protected $casts = [
        'scope_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
