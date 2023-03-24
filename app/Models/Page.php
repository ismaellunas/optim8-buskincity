<?php

namespace App\Models;

use App\Helpers\Url;
use App\Models\{
    Media,
    PageTranslation,
    User,
};
use App\Services\{
    PageService,
    SettingService,
};
use App\Traits\Mediable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model implements TranslatableContract
{
    use HasFactory;
    use Mediable;
    use Translatable;

    public $translatedAttributes = [
        'data',
        'excerpt',
        'locale',
        'meta_description',
        'meta_title',
        'plain_text_content',
        'settings',
        'slug',
        'status',
        'title',
    ];

    protected $translationModel = PageTranslation::class;

    public function newQuery(bool $excludeDeleted = true)
    {
        return $this->customNewQuery(
            parent::newQuery($excludeDeleted)
        );
    }

    protected function customNewQuery($newQuery): Builder
    {
        return $newQuery->whereNull('type');
    }

    public static function getStatusOptions(): array
    {
        return [
            [
                'id' => PageTranslation::STATUS_DRAFT,
                'value' => __('Draft'),
            ],
            [
                'id' => PageTranslation::STATUS_PUBLISHED,
                'value' => __('Published'),
            ]
        ];
    }

    public function saveFromInputs(array $inputs)
    {
        foreach ($inputs as $locale => $input) {
            if (!empty($inputs[$locale])) {
                if (array_key_exists('data', $input)) {
                    $inputs[$locale]['plain_text_content'] = PageService::transformComponentToText($input['data']);
                }

                if (!$this->translate($locale)) {
                    $inputs[$locale]['unique_key'] = Url::randomDigitSegment([PageTranslation::class, 'isUniqueKeyExist']);
                }
            }
        }

        $this->fill($inputs);
        $this->save();
    }

    public function syncMediaFromInputs(array $inputs): void
    {
        $locales = array_keys($inputs);

        if ($locales) {
            foreach ($locales as $locale) {
                $pageTranslation = $this->translate($locale);
                $mediaIds = collect($inputs[$locale]['data']['media'])
                    ->pluck('id')
                    ->all();

                $mediaIdExists = Media::whereIn('id', $mediaIds)
                    ->get()
                    ->pluck('id')
                    ->all();

                $pageTranslation->syncMedia($mediaIdExists);
            }
        }
    }

    public function saveAuthorId(int $authorId)
    {
        $this->author_id = $authorId;
        $this->save();
    }

    public function duplicatePage(Page $page)
    {
        $inputs = [];
        foreach ($page->translations as $translation) {
            $inputs[$translation->locale] = collect($translation->toArray())
                ->only([
                    'data',
                    'excerpt',
                    'locale',
                    'meta_description',
                    'meta_title',
                    'settings',
                    'slug',
                    'status',
                    'title',
                ])
                ->all();

            $inputs[$translation->locale]['title'] = $inputs[$translation->locale]['title'] . '-copy';
            $inputs[$translation->locale]['status'] = PageTranslation::STATUS_DRAFT;
            $inputs[$translation->locale]['slug'] = PageService::getUniqueSlug($inputs[$translation->locale]['slug']);
        }

        $this->saveFromInputs($inputs);
        $this->syncMediaFromInputs($inputs);
    }

    // Scopes:
    public function scopeSearch($query, string $term)
    {
        return $query->whereHas('translation', function ($query) use ($term) {
            $query
                ->where('title', 'ILIKE', '%'.$term.'%')
                ->orWhere('slug', 'ILIKE', '%'.$term.'%')
                ->orWhere('plain_text_content', 'ILIKE', '%'.$term.'%');
        });
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Relationships:
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function menuItems()
    {
        return $this->morphMany(MenuItem::class, 'menu_itemable');
    }

    // Accessors
    public function getHasMetaDescriptionAttribute(): bool
    {
        return !empty($this->meta_description);
    }

    public function getHasMetaTitleAttribute(): bool
    {
        return !empty($this->meta_title);
    }

    public function getStatusTextAttribute(): string
    {
        return collect(self::getStatusOptions())->first(function ($status, $key) {
            return $this->status == $status['id'];
        })['value'];
    }

    public function getAvailableTranslationsAttribute(): array
    {
        $languages = collect([]);
        foreach ($this->translations as $translation) {
            if ($translation->id) {
                $languages->push($translation->locale);
            }
        }

        return $languages->all();
    }

    public function getIsHomePageAttribute(): bool
    {
        $homePageId = app(SettingService::class)->getHomePage();

        if (!$homePageId) {
            return false;
        }

        return $this->id == $homePageId;
    }

    public function getUrlDefaultLocaleAttribute(): ?string
    {
        $pageTranslationSlug = $this->translateOrDefault(defaultLocale())
            ->slug
            ?? null;

        if (!$pageTranslationSlug) {
            return null;
        }

        return route('frontend.pages.show', $pageTranslationSlug);
    }
}
