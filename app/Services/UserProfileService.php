<?php

namespace App\Services;

use App\Models\{
    Media,
    User
};
use App\Services\ModuleService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Booking\Services\EventService;

class UserProfileService
{
    private $user;

    public function __construct()
    {
        $user = null;

        if (request()->route()) {
            $user = request()->route()->parameter('user');
        }

        if (is_string($user)) {
            $user = $this->getUser($user);
        }

        $this->user = $user;

        /*
         * TODO: This is a bandage, because produces an error.
         * TODO: I will be good if we remove abort() with throw an Exception
        if (!$this->user) {
            abort(404);
        }
         */
    }

    public function getMeta(string $key, string $locale = null): mixed
    {
        $meta = $this->user->metas->firstWhere('key', $key);

        if (is_null($meta)) {
            return null;
        }

        if (!is_null($locale)) {
            return $meta->value[$locale] ?? null;
        }

        return $meta->value;
    }

    private function getMedias(string $key): Collection
    {
        $mediaIds = $this->getMeta($key);

        if (!empty($mediaIds)) {
            return Media::select([
                    'extension',
                    'file_name',
                    'file_type',
                    'file_url',
                    'version',
                ])
                ->whereIn('id', $mediaIds)
                ->get();
        }

        return collect();
    }

    public function getMediaWithThumbnails(
        string $key,
        int $width,
        int $height
    ): Collection {
        $media = $this->getMedias($key);

        if ($media->isNotEmpty()) {
            return $media->map(function ($medium) use ($width, $height) {
                $mappedMedium = $medium->only('file_url', 'file_type');

                $mappedMedium['thumbnail_url'] = $medium->getThumbnailUrl(
                    $width,
                    $height
                );

                return $mappedMedium;
            });
        }

        return collect();
    }

    public function getCoverBackgroundUrl($width, $height): string
    {
        $media = $this->getMedias('cover_background_photo');

        if ($media->isNotEmpty()) {
            $medium = $media->first();

            return $medium->getOptimizedImageUrl($width, $height);
        }

        return "";
    }

    private function getUser(string $uniqueKey): ?User
    {
        return User::where('unique_key', $uniqueKey)->first();
    }

    public function isModuleBookingActivated(): bool
    {
        return app(ModuleService::class)->isModuleActive('Booking');
    }

    public function getUpcomingBookings(): LengthAwarePaginator
    {
        return app(EventService::class)->getUpcomingEventsByUser($this->user->id);
    }
}
