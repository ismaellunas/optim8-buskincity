<?php

namespace App\Models;

use App\Helpers\HumanReadable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use CloudinaryLabs\CloudinaryLaravel\Model\Media as CloudinaryMedia;
use Cloudinary\Transformation\Resize;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class Media extends CloudinaryMedia implements TranslatableContract
{
    use Translatable;

    const THUMBNAIL_HEIGHT = 300;
    const THUMBNAIL_WIDTH = 300;

    public $translatedAttributes = [
        'alt',
    ];

    public $fillable = [
        'file_name'
    ];

    protected $casts = [
        'assets' => AsCollection::class,
    ];

    public static $imageExtensions = [
        'jpeg',
        'jpg',
        'png',
    ];

    public function scopeImage($query)
    {
        return $query->whereIn('extension', self::$imageExtensions);
    }

    // Accessors:
    public function getIsImageAttribute(): bool
    {
        return in_array($this->extension, self::$imageExtensions);
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

    public function getReadableSizeAttribute(): string
    {
        return HumanReadable::bytesToHuman($this->size);
    }
}
