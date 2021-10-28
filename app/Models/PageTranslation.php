<?php

namespace App\Models;

use App\Casts\AsPageTranslationDataCollection;
use App\Contracts\PublishableInterface;
use App\Models\Page;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageTranslation extends Model implements PublishableInterface
{
    use HasFactory;
    use MediaAlly;

    protected $fillable = [
        'data',
        'except',
        'meta_description',
        'meta_title',
        'slug',
        'status',
        'title',
        'locale',
        'plain_text_content',
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

    public function scopeSearch($query, string $term)
    {
        return $query
            ->where('plain_text_content', 'ILIKE', '%'.$term.'%')
            ->orWhere('title', 'ILIKE', '%'.$term.'%')
            ->orWhere('excerpt', 'ILIKE', '%'.$term.'%');
    }
}
