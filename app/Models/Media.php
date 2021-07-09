<?php

namespace App\Models;

use CloudinaryLabs\CloudinaryLaravel\Model\Media as CloudinaryMedia;

class Media extends CloudinaryMedia
{
    public function scopeImage($query)
    {
        return $query->where('file_type', 'image');
    }
}
