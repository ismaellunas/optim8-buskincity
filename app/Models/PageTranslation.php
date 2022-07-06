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

    private function splitIdAndUniqueKey(string $uid)
    {
        $uniqueKey = substr($uid, -$this->uniqueKeyLength, $this->uniqueKeyLength);
        $id = substr($uid, 0, strlen($uid) - strlen($uniqueKey));

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

        foreach ($styledComponents as $styleBlocks) {
            foreach ($styleBlocks as $styleBlock) {
                $css .= $styleBlock->toText();
            }
        }

        $this->generated_style = MinifyCss::minify($css);
        $this->save();
    }

    public function getStyleUrlAttribute()
    {
        $uid = $this->id.$this->unique_key;

        return route('page.css', $uid);
    }

    private function getStyledComponents($entities): array
    {
        return collect($entities)
            ->filter(function ($entity) {
                $className = PageService::getEntityClassName($entity['componentName']);

                if (class_exists($className)) {
                    return in_array(
                        HasStyleInterface::class,
                        class_implements($className)
                    );
                }

                return false;
            })
            ->map(function ($entity) {
                $className = PageService::getEntityClassName($entity['componentName']);

                $entity = new $className($entity);

                return collect($entity->getStyleBlocks())
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
}
