<?php

namespace Modules\Booking\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;

class SearchEventController extends Controller
{
    public function __invoke()
    {
        return User::select([
                'id',
                'unique_key',
                'first_name',
                'last_name',
                'profile_photo_media_id',
            ])
            ->available()
            ->inRoleNames(['Performer'])
            ->hasPermissionNames(['public_page.profile'])
            ->with([
                'metas' => function ($q) {
                    $q->whereIn('key', [
                        'stage_name'
                    ]);
                },
                'profilePhoto' => function ($q) {
                    $q->select([
                        'id',
                        'extension',
                        'file_name',
                        'file_url',
                        'version',
                    ]);
                },
            ])
            ->limit(5)
            ->get()
            ->map(function ($user) {
                $metas = collect(['stage_name'])->mapWithKeys(function ($metaKey) use ($user) {
                    $value = $user->metas->pluck('value', 'key')->get($metaKey);
                    return [ $metaKey => $value ];
                })->all();

                return array_merge(
                    [
                        'id' => $user->unique_key,
                        'name' => $user->fullName,
                        'location' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin ornare magna eros, eu pellentesque tortor vestibulum ut. Maecenas non massa sem. Etiam finibus odio quis feugiat facilisis.',
                        'profile_photo_url' => (
                            $user->optimizedProfilePhotoUrl
                            ?? config('constants.profile_photo_path')
                        ),
                        'profile_page_url' => (
                            $user->hasPublicPage
                            ? $user->profilePageUrl
                            : null
                        ),
                    ],
                    $metas
                );
            });
    }
}
