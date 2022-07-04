<?php

namespace App\Models;

use App\Casts\AsPageTranslationDataCollection;
use App\Contracts\PublishableInterface;
use App\Models\Page;
use App\Traits\HasLocale;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageTranslation extends Model implements PublishableInterface
{
    use HasFactory;
    use HasLocale;
    use MediaAlly;

    protected $fillable = [
        'data',
        'excerpt',
        'generate_style',
        'locale',
        'meta_description',
        'meta_title',
        'plain_text_content',
        'slug',
        'status',
        'title',
        'unique_key',
    ];

    protected $casts = [
        'data' => AsPageTranslationDataCollection::class,
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }
}
