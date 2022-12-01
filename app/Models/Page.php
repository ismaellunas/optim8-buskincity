<?php

namespace App\Models;

use App\Helpers\Url;
use App\Models\{
    User,
    PageTranslation
};
use App\Services\{
    PageService,
    SettingService,
};
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model implements TranslatableContract
{
    use HasFactory;
    use MediaAlly;
    use Translatable;

    public $translatedAttributes = [
        'data',
        'excerpt',
        'meta_description',
        'meta_title',
        'slug',
        'status',
        'title',
        'locale',
        'plain_text_content',
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
                $inputs[$locale]['plain_text_content'] = PageService::transformComponentToText($input['data']);

                if (!$this->translate($locale)) {
                    $inputs[$locale]['unique_key'] = Url::randomDigitSegment([PageTranslation::class, 'isUniqueKeyExist']);
                }
            }
        }

        $this->fill($inputs);
        $this->save();

        $this->generateStylePageTranslation();
    }

    public function generateStylePageTranslation(): void
    {
        foreach ($this->translations as $pageTranslation) {
            $pageTranslation->generatePageStyle();
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
                    'locale',
                    'title',
                    'excerpt',
                    'data',
                    'slug',
                    'meta_title',
                    'meta_description',
                    'status',
                ])
                ->all();

            $inputs[$translation->locale]['title'] = $inputs[$translation->locale]['title'] . '-copy';
            $inputs[$translation->locale]['status'] = PageTranslation::STATUS_DRAFT;
            $inputs[$translation->locale]['slug'] = PageService::getUniqueSlug($inputs[$translation->locale]['slug']);
        }

        $this->saveFromInputs($inputs);
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
}
