<?php

namespace App\Models;

use App\Contracts\PublishableInterface;
use App\Helpers\HtmlToText;
use App\Models\Category;
use App\Services\PostService;
use App\Services\SettingService;
use App\Services\StorageService;
use App\Traits\HasLocale;
use App\Traits\Mediable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class Post extends BaseModel implements PublishableInterface
{
    use HasFactory;
    use HasLocale;
    use Mediable;

    const STATUS_SCHEDULED = 2;

    protected $fillable = [
        'content',
        'excerpt',
        'locale',
        'meta_description',
        'meta_title',
        'scheduled_at',
        'published_at',
        'slug',
        'status',
        'title',
        'plain_text_content',
        'is_cover_displayed',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
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

    public function menuItems()
    {
        return $this->morphMany(MenuItem::class, 'menu_itemable');
    }

    /* Scope: */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at');
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

    public function getIsUnpublishedAttribute(): bool
    {
        return $this->status !== self::STATUS_PUBLISHED;
    }

    public function getIsChangedToScheduledAttribute(): bool
    {
        return (
            $this->isScheduled
            && ($this->isDirty('scheduled_at') || $this->isDirty('status'))
        );
    }

    public function getIsChangedToUnpublishedAttribute(): bool
    {
        return (
            $this->isUnpublished
            && $this->isDirty('status')
        );
    }

    public function getCoverImageWithDimensionAttribute(): array
    {
        if ($this->coverImage) {
            $width = $this->coverImage['width'];
            $height = $this->coverImage['height'];

            if ($width > 700) {
                $height = $height * (700 / $width);
                $width = 700;
            }

            return [
                'url' => $this->coverImage->optimizedImageUrl,
                'width' => $width,
                'height' => $height,
            ];
        }

        return [];
    }

    private function removeWrappedParagraphFromShortcode(string $content): string
    {
        return preg_replace(
            '/(<[^>]*>.*)(\[form-builder.*?\])(.*<\/.+>)/',
            '$1$3$2',
            $content
        );
    }

    public function getPurifiedContentAttribute(): string
    {
        $content = Purifier::clean($this->content, 'tinymce');
        $content = $this->removeWrappedParagraphFromShortcode($content);

        return $content;
    }

    public function getCoverImageAttribute()
    {
        return $this->media->first();
    }

    /* Custom Methods: */
    public function getOptimizedThumbnailOrDefaultUrl(?int $width = null, ?int $height = null): string
    {
        $defaultDimensions = config('constants.dimensions.post_thumbnail');
        $width = $width ?? $defaultDimensions['width'];
        $height = $height ?? $defaultDimensions['height'];

        if ($this->coverImage) {
            return $this->coverImage->getOptimizedImageUrl($width, $height);
        }

        $seoPostThumbnail = app(SettingService::class)->getPostThumbnailMedia();

        if ($seoPostThumbnail) {
            return $seoPostThumbnail->getOptimizedImageUrl($width, $height);
        }

        return  StorageService::getImageUrl(config('constants.default_images.post_thumbnail'));
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

        if (!$this->published_at) {
            $this->published_at = Carbon::now();
        }

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

        if ($inputs['status'] == self::STATUS_SCHEDULED) {
            $this->scheduled_at = $inputs['scheduled_at'];
        } else {
            $this->scheduled_at = null;
        }

        if (
            !$this->published_at
            && $inputs['status'] == self::STATUS_PUBLISHED
        ) {
            $this->published_at = Carbon::now();
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

    public function getCategoryNames(): string
    {
        return $this->categories->implode('name', ', ');
    }
}
