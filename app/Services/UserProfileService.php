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

    public function getMedias(string $key): Collection
    {
        $mediaIds = $this->getMeta($key);

        if (!empty($mediaIds)) {
            return Media::select([
                    'file_url',
                    'file_type'
                ])
                ->whereIn('id', $mediaIds)
                ->get();
        }

        return collect([]);
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
