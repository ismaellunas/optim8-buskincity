<?php

namespace Modules\Space\Entities;

use App\Models\GlobalOption;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\PageTranslation;
use App\Models\User;
use App\Services\TranslationService;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Ecommerce\Entities\Product;

class Space extends Model implements TranslatableContract
{
    use HasFactory;
    use NodeTrait;
    use Translatable;

    public $translatedAttributes = [
        'condition',
        'description',
        'excerpt',
        'surface',
    ];

    protected $fillable = [
        'address',
        'contacts',
        'is_page_enabled',
        'latitude',
        'longitude',
        'name',
        'parent_id',
        'type_id',
    ];

    protected $casts = [
        'contacts' => 'array',
    ];

    protected static function newFactory()
    {
        return \Modules\Space\Database\factories\SpaceFactory::new();
    }

    public function managers()
    {
        return $this->belongsToMany(User::class);
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function type()
    {
        return $this->belongsTo(GlobalOption::class, 'type_id');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'medially');
    }

    public function logo()
    {
        return $this->hasOne(Media::class, 'id', 'logo_media_id');
    }

    public function cover()
    {
        return $this->hasOne(Media::class, 'id', 'cover_media_id');
    }

    public function events()
    {
        return $this->morphMany(SpaceEvent::class, 'eventable');
    }

    public function product()
    {
        return $this->morphOne(Product::class, 'productable');
    }

    public function menuItems()
    {
        return $this->morphMany(MenuItem::class, 'menu_itemable');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? $this->logo->file_url : null;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover ? $this->cover->file_url : null;
    }

    public function pageLocalizeURL(string $locale): string
    {
        $pageTranslation = $this->page->translate($locale, true);

        return LaravelLocalization::localizeURL(
            route('frontend.spaces.show', ['slugs' => $pageTranslation->getSlugs()]),
            $locale
        );
    }

    public function getOptimizedCoverImageUrl(
        ?int $width = null,
        ?int $height = null
    ): ?string {
        if ($this->cover) {
            return $this->cover->getOptimizedImageUrl($width, $height);
        }

        return null;
    }

    public function getOptimizedLogoImageUrl(
        ?int $width = null,
        ?int $height = null
    ): ?string {
        return $this->logo
            ? $this->logo->getOptimizedImageUrl($width, $height)
            : null;
    }

    public function saveFromInputs(array $inputs)
    {
        $this->name = $inputs['name'];
        $this->latitude = $inputs['latitude'];
        $this->longitude = $inputs['longitude'];
        $this->address = $inputs['address'];
        $this->type_id = $inputs['type_id'];
        $this->contacts = $inputs['contacts'] ?? [];

        if (array_key_exists('parent_id', $inputs)) {
            $this->parent_id = $inputs['parent_id'];
        }

        if (array_key_exists('is_page_enabled', $inputs)) {
            $this->is_page_enabled = $inputs['is_page_enabled'] ?? false;
        }

        if (!empty($inputs['translations'])) {
            $this->fill($inputs['translations']);
        }

        $this->save();
    }

    public function scopeTopParent($query)
    {
        return $query->whereNull('parent_id');
    }

    public function hasEnabledPage(): bool
    {
        return (
            $this->is_page_enabled
            && !empty($this->page)
            && $this->page->translations->contains('status', PageTranslation::STATUS_PUBLISHED)
        );
    }
}
