<?php

namespace App\Models;

use App\Helpers\HumanReadable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use CloudinaryLabs\CloudinaryLaravel\Model\Media as CloudinaryMedia;
use Cloudinary\Tag\ImageTag;
use Cloudinary\Transformation\Delivery;
use Cloudinary\Transformation\Quality;
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

    // Accessors:
    public function getFileNameWithoutExtensionAttribute(): string
    {
        if (!in_array($this->file_type, ['image', 'video'])) {
            return Str::replaceLast('.'.$this->extension, '', $this->file_name);
        }
        return $this->file_name;
    }

    public function getIsImageAttribute(): bool
    {
        return in_array($this->extension, config('constants.extensions.image'));
    }

    public function getThumbnailUrlAttribute(): string
    {
        $result = '';
        if ($this->isImage) {
            $result = cloudinary()
                ->getImageTag(
                    empty($this->version)
                    ? $this->file_name
                    : 'v'.$this->version.'/'.$this->file_name
                )
                ->resize(
                    Resize::thumbnail()
                        ->height(self::THUMBNAIL_HEIGHT)
                        ->width(self::THUMBNAIL_WIDTH)
                )
                ->serializeAttributes();
        }

        return strval(str_replace(['src=', '"'], ['', ''], $result));
    }

    public function getOptimizedImageUrlAttribute(): string
    {
        $result = "";

        if ($this->isImage) {
            $result = $this->optimizeImage()->serializeAttributes();
        }

        return strval(str_replace(['src=', '"'], ['', ''], $result));
    }

    public function getCroppedImageUrl(int $width, int $height): string
    {
        $result = "";

        if ($this->isImage) {
            $result = $this->optimizeImage()
                ->resize(
                    Resize::crop()
                        ->width($width)
                        ->height($height)
                )
                ->serializeAttributes();
        }

        return strval(str_replace(['src=', '"'], ['', ''], $result));
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

    private function optimizeImage(): ImageTag
    {
        return cloudinary()
                ->getImageTag(
                    empty($this->version)
                    ? $this->file_name
                    : 'v'.$this->version.'/'.$this->file_name
                )
                ->delivery(Delivery::quality(Quality::auto()));
    }
}
