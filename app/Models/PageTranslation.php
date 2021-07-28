<?php

namespace App\Models;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;

class PageTranslation extends Model
{
    use HasFactory;
    use MediaAlly;

    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;

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
        'data' => AsCollection::class,
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
