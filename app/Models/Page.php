<?php

namespace App\Models;

use App\Models\PageTranslation;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model implements TranslatableContract
{
    use HasFactory;
    use MediaAlly;
    use Translatable;

    public $translatedAttributes = [
        'data',
        'excerpt',
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
                'id' => PageTranslation::STATUS_DRAFT,
                'value' => __('Draft'),
            ],
            [
                'id' => PageTranslation::STATUS_PUBLISHED,
                'value' => __('Published'),
            ]
        ];
    }

    public function saveFromInputs(array $inputs)
    {
        $this->fill($inputs);
        $this->save();
    }

    public function saveAuthorId(int $authorId)
    {
        $this->author_id = $authorId;
        $this->save();
    }

    // Scopes:
    public function scopeSearch($query, string $term)
    {
        return $query->whereHas('translation', function ($query) use ($term) {
            $query
                ->where('title', 'ILIKE', '%'.$term.'%')
                ->orWhere('slug', 'ILIKE', '%'.$term.'%');
        });
    }

    // Relationships:
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
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
