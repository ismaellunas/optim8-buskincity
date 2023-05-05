<?php

namespace App\Models;

use App\Helpers\HumanReadable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use CloudinaryLabs\CloudinaryLaravel\Model\Media as CloudinaryMedia;
use Cloudinary\Tag\ImageTag;
use Cloudinary\Tag\VideoTag;
use Cloudinary\Transformation\Delivery;
use Cloudinary\Transformation\Format;
use Cloudinary\Transformation\Resize;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Media extends CloudinaryMedia implements TranslatableContract
{
    use HasFactory;
    use Translatable;

    const THUMBNAIL_HEIGHT = 300;
    const THUMBNAIL_WIDTH = 300;
    const TYPE_DEFAULT = 0;
    const TYPE_SETTING = 1;
    const TYPE_PROFILE = 2;
    const TYPE_USER_META = 3;

    public $translatedAttributes = [
        'alt',
        'description',
    ];

    public $fillable = [
        'assets',
        'file_name',
        'file_url',
        'file_type',
        'extension',
        'version',
        'type',
    ];

    protected $casts = [
        'assets' => AsCollection::class,
    ];

    // Relationships:
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function medially(): MorphTo
    {
        return $this->morphTo();
    }

    public function mediable()
    {
        return $this->hasMany(Mediable::class, 'media_id');
    }

    // Scopes:
    public function scopeImage($query)
    {
        return $query->whereIn('extension', config('constants.extensions.image'));
    }

    public function scopeVideo($query)
    {
        return $query->whereIn('extension', config('constants.extensions.video'));
    }

    public function scopeDocument($query)
    {
        return $query->whereIn('extension', config('constants.extensions.document'));
    }

    public function scopeSpreadsheet($query)
    {
        return $query->whereIn('extension', config('constants.extensions.spreadsheet'));
    }

    public function scopePresentation($query)
    {
        return $query->whereIn('extension', config('constants.extensions.presentation'));
    }

    public function scopeDefault($query)
    {
        return $query->where('type', self::TYPE_DEFAULT);
    }

    public function scopeSetting($query)
    {
        return $query->where('type', self::TYPE_SETTING);
    }

    public function scopeProfile($query)
    {
        return $query->where('type', self::TYPE_PROFILE);
    }

    public function scopeUploader($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Accessors:
    public function getFileNameWithoutExtensionAttribute(): string
    {
        $slice = Str::afterLast($this->file_name, '/');

        if (!in_array($this->file_type, ['image', 'video'])) {
            return Str::replaceLast('.'.$this->extension, '', $slice);
        }
        return $slice;
    }

    public function getIsImageAttribute(): bool
    {
        return in_array($this->extension, config('constants.extensions.image'));
    }

    public function getIsVideoAttribute(): bool
    {
        return $this->file_type === 'video';
    }

    private function getImageTag(): ImageTag
    {
        return cloudinary()->getImageTag(
            empty($this->version)
            ? $this->file_name
            : 'v'.$this->version.'/'.$this->file_name
        );
    }

    private function getVideoTag(): VideoTag
    {
        return cloudinary()->getVideoTag(
            empty($this->version)
            ? $this->file_name
            : 'v'.$this->version.'/'.$this->file_name
        );
    }

    private function getImageUrlFromAttributeTag(string $attributeTag)
    {
        return Str::before(
            strval(str_replace(['src=', '"'], ['', ''], $attributeTag)),
            '?_a='
        );
    }

    private function getVideoUrlFromAttributeTag(string $attributeTag)
    {
        return strval(str_replace(['poster=', '"'], ['', ''], $attributeTag));
    }

    public function getThumbnailUrl($width, $height): string
    {
        $result = '';

        if ($this->isImage) {
            $result = $this->getImageTag()
                ->resize(Resize::thumbnail()->height($height)->width($width))
                ->serializeAttributes();

            $result = $this->getImageUrlFromAttributeTag($result);
        }

        if ($this->isVideo) {
            $result = $this->getVideoTag()
                ->resize(Resize::thumbnail()->height($height)->width($width))
                ->serializeAttributes();

            $result = $this->getVideoUrlFromAttributeTag($result);
        }

        return $result;
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->getThumbnailUrl(self::THUMBNAIL_HEIGHT, self::THUMBNAIL_WIDTH);
    }

    private function optimizeImage(
        ?int $width = null,
        ?int $height = null,
        string $resizeMode = 'fill'
    ): ImageTag {
        $imageTag = $this->getImageTag();

        if ($width || $height) {
            $imageTag->resize(Resize::$resizeMode($width, $height));
        }

        return $imageTag->delivery(Delivery::format(Format::auto()));
    }

    public function getOptimizedImageUrl(
        ?int $width = null,
        ?int $height = null,
        string $resizeMode = 'fill'
    ): string {
        $result = "";

        if ($this->isImage) {
            $result = $this->optimizeImage($width, $height, $resizeMode)->serializeAttributes();
        }

        return $this->getImageUrlFromAttributeTag($result);
    }

    public function getOptimizedImageUrlAttribute(): string
    {
        return $this->getOptimizedImageUrl();
    }

    public function getReadableSizeAttribute(): string
    {
        return HumanReadable::bytesToHuman($this->size);
    }

    public function getIsDefaultTypeAttribute(): bool
    {
        return $this->type == self::TYPE_DEFAULT;
    }

    public function getDisplayFileNameAttribute(): string
    {
        $slice = Str::afterLast($this->file_name, '/');

        if (in_array($this->file_type, ['image', 'video'])) {
            return $slice.'.'.$this->extension;
        }

        return $slice;
    }

    public function getCanDeletedAttribute(): bool
    {
        return $this->mediable->isEmpty();
    }

    public function saveUserId(int $userId): void
    {
        $this->user_id = $userId;
        $this->save();
    }
}
