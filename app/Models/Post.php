<?php

namespace App\Models;

use App\Contracts\PublishableInterface;
use App\Helpers\HtmlToText;
use App\Models\Category;
use App\Models\Media;
use App\Services\PostService;
use App\Traits\HasLocale;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Post extends BaseModel implements PublishableInterface
{
    use HasFactory;
    use HasLocale;

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
        'plain_text_content',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    /* Relationship: */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)
            ->withPivot('is_primary');
    }

    public function primaryCategories()
    {
        return $this->belongsToMany(Category::class)
            ->wherePivot('is_primary', true);
    }

    public function getCategoryAttribute(): ?Category
    {
        return $this->primaryCategories->first();
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

    public function scopeInCategories($query, array $categoryIds)
    {
        return $query->whereHas(
            'categories',
            function ($query) use ($categoryIds) {
                $query->whereIn(Category::getTableName().'.id', $categoryIds);
            }
        );
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->whereHas(
            'categories',
            function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            }
        );
    }

    public function scopeSearch($query, string $term)
    {
        return $query
            ->where('title', 'ILIKE', '%'.$term.'%')
            ->orWhere('excerpt', 'ILIKE', '%'.$term.'%')
            ->orWhere('slug', 'ILIKE', '%'.$term.'%')
            ->orWhere('plain_text_content', 'ILIKE', '%'.$term.'%');
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

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->coverImage->file_url ?? null;
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

    public function saveFromInputs(array $inputs)
    {
        $inputs['plain_text_content'] = HtmlToText::convert($inputs['content']);

        $this->fill($inputs);

        $this->slug = PostService::getUniqueSlug(
            Str::slug($inputs['slug'], '-', $this->locale),
            ($this->id ? [$this->id] : null)
        );

        if ($inputs['status'] == Post::STATUS_SCHEDULED) {
            $this->scheduled_at = $inputs['scheduled_at'];
        } else {
            $this->scheduled_at = null;
        }

        return $this->save();
    }

    public function syncCategories(array $categoryIds, string $primaryCategoryId = null)
    {
        $categories = [];

        foreach ($categoryIds as $categoryId) {
            $categories[$categoryId] = [
                'is_primary' => $categoryId == $primaryCategoryId
            ];
        }

        return $this->categories()->sync($categories);
    }
}
