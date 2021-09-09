<?php

namespace App\Models;

use App\Contracts\PublishableInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements PublishableInterface
{
    use HasFactory;

    protected $fillable = [
        'content',
        'cover',
        'excerpt',
        'locale',
        'meta_description',
        'meta_title',
        'scheduled_on',
        'slug',
        'status',
        'title',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
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
            ]
        ];
    }

}
