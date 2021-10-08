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
}
