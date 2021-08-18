<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use CloudinaryLabs\CloudinaryLaravel\Model\Media as CloudinaryMedia;
use Cloudinary\Transformation\Resize;

class Media extends CloudinaryMedia implements TranslatableContract
{
    use Translatable;

    public $translatedAttributes = [
        'alt',
    ];

    public $fillable = [
        'file_name'
    ];

    public function scopeImage($query)
    {
        return $query->where('file_type', 'image');
    }

    // Accessors:
    public function getThumbnailUrlAttribute()
    {
        $result = cloudinary()
           ->getImageTag($this->file_name)
           ->resize(Resize::thumbnail()->width(300)->height(300))
           ->serializeAttributes();

        return strval(str_replace(['src=', '"'], ['', ''], $result));
    }
}
