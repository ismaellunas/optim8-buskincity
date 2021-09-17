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
        'scheduled_at',
        'slug',
        'status',
        'title',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
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

    /* Scope: */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeAlreadyPublishedAt($query, string $dateTime)
    {
        return $query->where(function ($query) use ($dateTime) {
            $query
                ->where(function ($query) use ($dateTime) {
                    $query
                        ->scheduled()
                        ->where('scheduled_at', '<=', $dateTime);
                })
                ->orWhere->published();
        });
    }

    public function scopeSearch($query, string $term)
    {
        return $query
            ->where('title', 'ILIKE', '%'.$term.'%')
            ->orWhere('content', 'ILIKE', '%'.$term.'%')
            ->orWhere('excerpt', 'ILIKE', '%'.$term.'%')
            ->orWhere('slug', 'ILIKE', '%'.$term.'%');
    }

    /* Accessors: */
    public function getIsScheduledAttribute(): bool
    {
        return $this->status == self::STATUS_SCHEDULED;
    }

    public function getIsChangedToScheduledAttribute(): bool
    {
        return (
            $this->isScheduled
            && ($this->isDirty('scheduled_at') || $this->isDirty('status'))
        );
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

    public function publish(): void
    {
        $this->status = Post::STATUS_PUBLISHED;
        $this->scheduled_at = null;
        $this->save();
    }
}
