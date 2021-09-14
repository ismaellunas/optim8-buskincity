<?php

namespace App\Models;

use App\Contracts\PublishableInterface;
use App\Models\Category;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements PublishableInterface
{
    use HasFactory;

    const STATUS_SCHEDULED = 2;

    protected $fillable = [
        'content',
        'cover_image_id',
        'excerpt',
        'locale',
        'meta_description',
        'meta_title',
        'scheduled_on',
        'slug',
        'status',
        'title',
    ];

    /* Relationship: */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function coverImage()
    {
        return $this->hasOne(Media::class, 'id', 'cover_image_id');
    }

    public static function getStatusOptions(): array
    {
        return [
            [
                'id' => PublishableInterface::STATUS_DRAFT,
                'value' => __('Draft'),
            ],
            [
                'id' => PublishableInterface::STATUS_PUBLISHED,
                'value' => __('Published'),
            ],
            [
                'id' => self::STATUS_SCHEDULED,
                'value' => __('Scheduled'),
            ],
        ];
    }

}
