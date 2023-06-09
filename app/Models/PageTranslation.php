<?php

namespace App\Models;

use App\Casts\AsPageTranslationDataCollection;
use App\Contracts\HasStyleInterface;
use App\Contracts\PublishableInterface;
use App\Entities\Enums\PageSettingLayout;
use App\Helpers\MinifyCss;
use App\Helpers\Url;
use App\Models\Page;
use App\Services\PageService;
use App\Traits\HasLocale;
use App\Traits\Mediable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class PageTranslation extends BaseModel implements PublishableInterface
{
    use HasFactory;
    use HasLocale;
    use Mediable;

    private $uniqueKeyLength = 6;

    protected $fillable = [
        'data',
        'excerpt',
        'generated_style',
        'locale',
        'meta_description',
        'meta_title',
        'plain_text_content',
        'settings',
        'slug',
        'status',
        'title',
        'unique_key',
    ];

    protected $casts = [
        'data' => AsPageTranslationDataCollection::class,
        'settings' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeUid($query, $uid)
    {
        $splitUid = $this->splitIdAndUniqueKey($uid);

        return $query->where('id', $splitUid['id'])
            ->where('unique_key', $splitUid['uniqueKey']);
    }

    public function scopeType($query, string $type = null)
    {
        return $query->whereHas('page', function (Builder $q) use ($type) {
            if ($type) {
                $q->type($type);
            } else {
                $q->whereNull('type');
            }
        });
    }

    private function splitIdAndUniqueKey(string $uid)
    {
        $timestampLength = 12;

        $uniqueKey = substr($uid, (-$this->uniqueKeyLength - $timestampLength), $this->uniqueKeyLength);
        $id = substr($uid, 0, strlen($uid) - strlen($uniqueKey) - $timestampLength);

        return [
            "id" => $id,
            "uniqueKey" => $uniqueKey
        ];
    }

    private function generatePageStyle(): string
    {
        $css = "";

        $styledComponents = $this->getStyledComponents(
            ($this->data instanceof Collection)
                ? $this->data->get('entities')
                : $this->data->entities ?? []
        );

        $css .= $this->getDesktopCssFromStyleBlocks($styledComponents['desktop']);

        $css .= $this->getMobileCssStyleBlocks($styledComponents['mobile']);

        return MinifyCss::minify($css);
    }

    public function setGeneratedStyle(): void
    {
        $this->generated_style = $this->generatePageStyle();
    }

    private function getCssFromStyleBlocks(array $styledComponents): string
    {
        $css = "";

        foreach ($styledComponents as $styleBlocks) {
            foreach ($styleBlocks as $styleBlock) {
                $css .= $styleBlock->toText();
            }
        }

        return $css;
    }

    private function getDesktopCssFromStyleBlocks(array $styledComponents): string
    {
        $css = $this->getCssFromStyleBlocks($styledComponents);
        return "@media screen and (min-width: 1024px) { $css }";
    }

    private function getMobileCssStyleBlocks(array $styledComponents): string
    {
        $template = "@media screen and (max-width: 1023px) {:css}";

        return preg_replace_array(
            '/:[a-z_]+/',
            [$this->getCssFromStyleBlocks($styledComponents)],
            $template
        );
    }

    public function getStyleUrlAttribute()
    {
        $uid = $this->id.$this->unique_key.$this->updated_at->format('dmyHis');

        return route('page.css', $uid);
    }

    private function getStyledComponents($entities): array
    {
        $entities = collect($entities)->filter(function ($entity) {
            $className = PageService::getEntityClassName($entity['componentName']);

            return (
                $className
                && in_array(HasStyleInterface::class, class_implements($className))
            );
        });

        return [
            'desktop' => $this->getStyleBlocks($entities),
            'mobile' => $this->getMobileStyleBlocks($entities),
        ];
    }

    private function getStyleBlocks($entities): array
    {
        return $entities
            ->map(function ($entity) {
                $className = PageService::getEntityClassName($entity['componentName']);

                if ($className) {
                    $entity = new $className($entity);

                    $styleBlocks = $entity->getStyleBlocks();

                    return collect($styleBlocks)
                        ->filter(function ($styleBlock) {
                            return !$styleBlock->isEmpty();
                        });
                }

                return null;
            })
            ->filter()
            ->all();
    }

    private function getMobileStyleBlocks($entities): array
    {
        return $entities
            ->map(function ($entity) {
                $className = PageService::getEntityClassName($entity['componentName']);

                if ($className) {
                    $entity = new $className($entity);

                    $styleBlocks = $entity->getMobileStyleBlocks();

                    return collect($styleBlocks)
                        ->filter(function ($styleBlock) {
                            return !$styleBlock->isEmpty();
                        });
                }

                return null;
            })
            ->filter()
            ->all();
    }

    public function hasUniqueKey(): bool
    {
        return $this->unique_key != null;
    }

    public static function isUniqueKeyExist(string $uniqueKey): bool
    {
        return self::where('unique_key', $uniqueKey)->exists();
    }

    public function setUniqueKey()
    {
        $this->unique_key = Url::randomDigitSegment([$this, 'isUniqueKeyExist']);
    }

    protected static function booted()
    {
        static::addGlobalScope('pageTranslation', function (Builder $query) {
            $query->type();
        });
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->status == PageTranslation::STATUS_DRAFT;
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status == PageTranslation::STATUS_PUBLISHED;
    }

    public function getIsLayoutNoHeaderAndFooterAttribute(): string
    {
        $layout = $this->getSettingValueByKey('layout');

        return PageSettingLayout::NO_HEADER_AND_FOOTER->value == $layout;
    }

    public function getSettingValueByKey(string $key): ?string
    {
        return $this->settings[$key] ?? null;
    }

    public function isClearingMenuCacheRequired(): bool
    {
        if ($this->page->menuItems->isNotEmpty()) {
            return collect($this->getChanges())
                ->keys()
                ->contains(fn ($attribute) => in_array($attribute, [
                    'title',
                    'slug',
                    'status',
                ]));
        }

        return false;
    }
}
