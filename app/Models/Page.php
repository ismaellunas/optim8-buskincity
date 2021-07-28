<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;

class Page extends Model implements TranslatableContract
{
    use HasFactory;
    use MediaAlly;
    use Translatable;

    const STATUS_DRAFT = 0;
    const STATUS_PUBLISH = 1;

    public $translatedAttributes = [
        'data',
        'except',
        'meta_description',
        'meta_title',
        'slug',
        'status',
        'title',
    ];

    public static function getStatusOptions(): array
    {
        return [
            [
                'id' => self::STATUS_DRAFT,
                'value' => __('Draft'),
            ],
            [
                'id' => self::STATUS_PUBLISH,
                'value' => __('Published'),
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
