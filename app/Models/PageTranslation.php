<?php

namespace App\Models;

use App\Casts\AsPageTranslationDataCollection;
use App\Contracts\HasStyleInterface;
use App\Contracts\PublishableInterface;
use App\Helpers\MinifyCss;
use App\Helpers\Url;
use App\Models\Page;
use App\Services\PageService;
use App\Traits\HasLocale;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageTranslation extends Model implements PublishableInterface
{
    use HasFactory;
    use HasLocale;
    use MediaAlly;

    private $uniqueKeyLength = 6;

    protected $fillable = [
        'data',
        'excerpt',
        'generated_style',
        'locale',
        'meta_description',
        'meta_title',
        'plain_text_content',
        'slug',
        'status',
        'title',
        'unique_key',
    ];

    protected $casts = [
        'data' => AsPageTranslationDataCollection::class,
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

    public function generatePageStyle(): void
    {
        $css = "";

        $styledComponents = $this->getStyledComponents(
            $this->data->get('entities')
        );

        $css .= $this->getCssStyleBlocks($styledComponents['desktop']);

        $css .= $this->getMobileCssStyleBlocks($styledComponents['mobile']);

        $this->generated_style = MinifyCss::minify($css);
        $this->save();
    }

    private function getCssStyleBlocks(array $styledComponents): string
    {
        $css = "";

        foreach ($styledComponents as $styleBlocks) {
            foreach ($styleBlocks as $styleBlock) {
                $css .= $styleBlock->toText();
            }
        }

        return $css;
    }

    private function getMobileCssStyleBlocks(array $styledComponents): string
    {
        $template = "@media screen and (max-width: 1023px) {:css}";

        return preg_replace_array(
            '/:[a-z_]+/',
            [$this->getCssStyleBlocks($styledComponents)],
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
        $entities = collect($entities)
            ->filter(function ($entity) {
                $className = PageService::getEntityClassName($entity['componentName']);

                if (class_exists($className)) {
                    return in_array(
                        HasStyleInterface::class,
                        class_implements($className)
                    );
                }

                return false;
            });

        return [
            'desktop' => $this->getStyleBlocks($entities),
            'mobile' => $this->getMobileStyleBlocks($entities),
        ];
    }

    private function getStyleBlocks($entities): array
    {
        return $entities->map(function ($entity) {
                $className = PageService::getEntityClassName($entity['componentName']);

                $entity = new $className($entity);

                $styleBlocks = $entity->getStyleBlocks();

                return collect($styleBlocks)
                    ->filter(function ($styleBlock) {
                        return !$styleBlock->isEmpty();
                    });
            })
            ->all();
    }

    private function getMobileStyleBlocks($entities): array
    {
        return $entities->map(function ($entity) {
                $className = PageService::getEntityClassName($entity['componentName']);

                $entity = new $className($entity);

                $styleBlocks = $entity->getMobileStyleBlocks();

                return collect($styleBlocks)
                    ->filter(function ($styleBlock) {
                        return !$styleBlock->isEmpty();
                    });
            })
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

    public function hasGeneratedStyle(): bool
    {
        return $this->generated_style != null;
    }

    protected static function booted()
    {
        static::addGlobalScope('pageTranslation', function (Builder $query) {
            $query->type();
        });
    }
}
