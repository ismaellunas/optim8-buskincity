<?php

namespace App\Traits;

use App\Models\Media;

trait Mediable
{
    // Relations
    public function media()
    {
        return $this->morphToMany(Media::class, 'mediable')
            ->withTimestamps();
    }

    // Custom Methods
    public function syncMedia(array $mediaIds = []): void
    {
        $mediaIds = collect($mediaIds)
            ->filter()
            ->map(fn($mediaId) => (int)$mediaId)
            ->all();

        if (!empty($mediaIds)) {
            $this->media()->sync($mediaIds);
        } else {
            $this->detachMedia();
        }
    }

    public function detachMedia(?int $mediaId = null): void
    {
        $this->media()->detach($mediaId);
    }
}
