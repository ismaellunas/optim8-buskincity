<?php

namespace App\Traits;

use App\Models\Media;

trait Mediable
{
    // Relations
    public function media()
    {
        return $this->morphToMany(Media::class, 'mediable');
    }

    // Custom Methods
    public function syncMedia(array $mediaIds = []): void
    {
        if (!empty($mediaIds)) {
            $this->media()->sync($mediaIds);
        } else {
            $this->detachMedia();
        }
    }

    public function detachMedia(): void
    {
        $this->media()->detach();
    }
}
