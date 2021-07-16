<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;

class Page extends Model
{
    use HasFactory;
    use MediaAlly;

    const STATUS_DRAFT = 0;
    const STATUS_PUBLISH = 1;

    protected $casts = [
        'data' => AsCollection::class,
    ];

    protected $fillable = [
        'data',
        'meta_description',
        'meta_title',
        'slug',
        'title',
        'status',
    ];

    public static function getStatusOptions(): array
    {
        return [
            [
                'id' => self::STATUS_DRAFT,
                'value' => 'Draft',
            ],
            [
                'id' => self::STATUS_PUBLISH,
                'value' => 'Publish',
            ]
        ];
    }

    // Accessors
    public function getHasMetaDescriptionAttribute(): bool
    {
        return !empty($this->meta_description);
    }

    public function getHasMetaTitleAttribute(): bool
    {
        return !empty($this->meta_title);
    }

    public function getStatusTextAttribute(): string
    {
        return collect(self::getStatusOptions())->first(function ($status, $key) {
            return $this->status == $status['id'];
        })['value'];
    }
}
