<?php

namespace App\Models;

use App\Entities\Components\Text;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use App\Models\PageTranslation;

class Page extends Model implements TranslatableContract
{
    use HasFactory;
    use MediaAlly;
    use Translatable;

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
                'id' => PageTranslation::STATUS_DRAFT,
                'value' => __('Draft'),
            ],
            [
                'id' => PageTranslation::STATUS_PUBLISHED,
                'value' => __('Published'),
            ]
        ];
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

    // Store to new column
    public function storeEntitiesAsPlainText()
    {
        $string = "";
        foreach($this->data['entities'] as $entity)
        {
            $class = '\\App\\Entities\\Components\\' . $entity['componentName'];
            $class = new $class($entity);
            $string .= $class->getText() . ' ';
        }

        dd(trim($string));
    }
}
