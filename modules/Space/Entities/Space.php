<?php

namespace Modules\Space\Entities;

use App\Models\BaseModel;
use App\Models\GlobalOption;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\User;
use App\Services\CountryService;
use App\Traits\Mediable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;
use Kalnoy\Nestedset\NodeTrait;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Ecommerce\Entities\Product;
use Modules\Space\Entities\Page;
use Modules\Space\Entities\PageTranslation;
use Modules\Space\ModuleService;

class Space extends BaseModel implements TranslatableContract
{
    use HasFactory;
    use Mediable;
    use NodeTrait;
    use Translatable;

    const TYPE_LOGO = 'logo';
    const TYPE_COVER = 'cover';

    public $translatedAttributes = [
        'condition',
        'description',
        'excerpt',
        'surface',
    ];

    protected $fillable = [
        'address',
        'city',
        'contacts',
        'country_code',
        'is_page_enabled',
        'latitude',
        'longitude',
        'name',
        'parent_id',
        'type_id',
    ];

    protected $casts = [
        'contacts' => 'array',
        'latitude' => 'double',
        'longitude' => 'double',
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

    public function logoMedia()
    {
        return $this->media()->wherePivot('type', self::TYPE_LOGO);
    }

    public function getLogoAttribute(): ?Media
    {
        return $this->logoMedia->first() ?? null;
    }

    public function coverMedia()
    {
        return $this->media()->wherePivot('type', self::TYPE_COVER);
    }

    public function getCoverAttribute(): ?Media
    {
        return $this->coverMedia->first() ?? null;
    }

    public function events()
    {
        return $this->hasMany(SpaceEvent::class);
    }

    public function product()
    {
        return $this->morphOne(Product::class, 'productable');
    }

    public function menuItems()
    {
        return $this->morphMany(MenuItem::class, 'menu_itemable');
    }

    public function pageTranslations()
    {
        return $this->hasManyThrough(
            PageTranslation::class,
            Page::class,
            'id',
            'page_id',
            'page_id',
            'id'
        );
    }

    public function scopeIsPageEnabled($query, bool $isEnabled = true)
    {
        return $query->where('is_page_enabled', $isEnabled);
    }

    public function scopeWithStructuredUrl($query, array $locales = [])
    {
        $pageTable = PageTranslation::getTableName();
        $spaceTable = Space::getTableName();

        $tableColumns = [
            'space' => collect(['id', 'page_id', '_lft', '_rgt', 'parent_id', 'is_page_enabled', 'type_id', 'updated_at'])
                ->map(fn ($column) => $spaceTable.'.'.$column)->all(),
            'pageTranslations' => collect(['id', 'page_id', 'locale', 'status', 'slug', 'unique_key'])
                ->map(fn ($column) => $pageTable.'.'.$column)->all(),
        ];

        $query->with([
            'page' => function ($query) use ($locales, $tableColumns) {
                $query->select('id', 'type');
                $query->with('translations', function ($query) use ($locales, $tableColumns) {
                    $columns = collect(['id', 'page_id', 'locale', 'status', 'updated_at']);
                    $query->select($columns->map(fn ($column) => PageTranslation::getTableName().'.'.$column)->all());
                    if ($locales) {
                        $query->inLanguages($locales);
                    }
                    $query->with('space', function ($query) use ($locales, $tableColumns) {
                        $query->select($tableColumns['space']);
                        $query->with([
                            'pageTranslations' => function ($query) use ($locales, $tableColumns) {
                                $query->select($tableColumns['pageTranslations']);
                                if ($locales) {
                                    $query->inLanguages($locales);
                                }
                            },
                            'ancestors' => function ($query) use ($locales, $tableColumns) {
                                $query->select($tableColumns['space']);
                                $query->with('pageTranslations', function ($query) use ($locales, $tableColumns) {
                                    $query->select($tableColumns['pageTranslations']);
                                    if ($locales) {
                                        $query->inLanguages($locales);
                                    }
                                });
                            },
                        ]);
                    });
                });
            },
        ]);
    }

    public function scopeSearch($query, string $term)
    {
        $query->where('name', 'ILIKE', '%'.$term.'%');
    }

    public function scopeInType($query, array $types = [])
    {
        $query->whereIn('type_id', $types);
    }

    public function getIsParentableAttribute(): bool
    {
        return $this->depth < ModuleService::maxParentDepth();
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
        return $this->cover
            ? $this->cover->getOptimizedImageUrl($width, $height)
            : null;
    }

    public function getOptimizedLogoImageUrl(
        ?int $width = null,
        ?int $height = null
    ): string {
        return $this->logo
            ? $this->logo->getOptimizedImageUrl($width, $height)
            : ModuleService::defaultLogoUrl();
    }

    public function saveFromInputs(array $inputs)
    {
        $this->name = $inputs['name'];
        $this->latitude = $inputs['latitude'] ?? null;
        $this->longitude = $inputs['longitude'] ?? null;
        $this->address = $inputs['address'] ?? null;
        $this->type_id = $inputs['type_id'] ?? null;
        $this->contacts = $inputs['contacts'] ?? [];
        $this->city = Arr::get($inputs, 'city');
        $this->country_code = Arr::get($inputs, 'country_code');

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

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address,
            $this->city,
            (
                $this->country_code
                ? app(CountryService::class)->getCountryName($this->country_code)
                : null
            ),
        ])->filter()->implode(', ');
    }

    public function getTypeNameAttribute(): string
    {
        return $this->type->name ?? "";
    }
}
