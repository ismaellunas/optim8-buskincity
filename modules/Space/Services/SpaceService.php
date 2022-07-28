<?php

namespace Modules\Space\Services;

use App\Contracts\MediaStorageInterface as MediaStorage;
use App\Models\Media;
use App\Models\User;
use App\Services\GlobalOptionService;
use App\Services\MediaService;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Space\Entities\Space;
use Modules\Space\ModuleService;

class SpaceService
{
    private $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    private function filterRootIds(array $ids)
    {
        $spaceIds = collect($ids);

        foreach ($ids as $spaceId) {
            $descendantIds = Space::whereDescendantOf($spaceId)
                ->get('id')
                ->pluck('id')
                ->all();

            $spaceIds = $spaceIds->diff($descendantIds);
        }

        return $spaceIds;
    }

    private function spaceRoots(array $ids = null): Collection
    {
        $roots = collect();
        $spaceIds = null;

        if (!is_null($ids)) {
            $spaceIds = $this->filterRootIds($ids);
        }

        Space::select(['id', 'parent_id', '_lft', '_rgt'])
            ->when(is_null($ids), function ($query) {
                $query->whereNull('parent_id');
            })
            ->when($spaceIds, function ($query, $spaceIds) {
                $query->whereIn('id', $spaceIds->all());
            })
            ->get()
            ->each(function ($space) use ($roots) {
                $tree = Space::select(['id', 'name', 'parent_id', '_lft', '_rgt'])
                    ->withDepth()
                    ->descendantsAndSelf($space);

                $roots->push($tree);
            });

        return $roots;
    }

    public function spaceTree(Authenticatable $user, int $parentId = null): Collection
    {
        if (! is_null($parentId)) {
            $spaceIds = [$parentId];
        } else {
            $spaceIds = null;

            if (! $user->can('space.viewAny')) {
                $spaceIds = $user->spaces->pluck('id')->all();
            }
        }

        $roots = $this->spaceRoots($spaceIds);

        return $roots->map(function ($root) {
            return $root->toTree()->first();
        });
    }

    public function parentOptions(
        Authenticatable $user,
        bool $isEmptyAllowed = true
    ): Collection {
        $spaceIds = null;

        if (! $user->can('space.viewAny')) {
            $spaceIds = $user->spaces->pluck('id')->all();
        }

        $options = collect();

        if ($isEmptyAllowed) {
            $options->push(['id' => null, 'value' => __('None')]);
        }

        $roots = $this->spaceRoots($spaceIds);

        $roots->each(function ($root) use ($options) {
            foreach ($root as $space) {
                if ($space->depth <= 1) {
                    $options->push([
                        'id' => $space->id,
                        'value' => $space->name,
                    ]);
                }
            }
        });

        return $options;
    }

    public function managers(
        string $term = null,
        array $excludedIds = [],
        int $limit = 15
    ): Collection {

        return User::available()
            ->backend()
            ->notInRoleNames([config('permission.super_admin_role')])
            ->when($term, function ($query, $term) {
                $query->search($term);
            })
            ->when($excludedIds, function ($query, $excludedIds) {
                $query->whereNotIn('id', $excludedIds);
            })
            ->limit($limit)
            ->get([
                'id',
                'first_name',
                'last_name',
            ])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'value' => $user->fullName,
                ];
            });
    }

    public function types(): Collection
    {
        return app(GlobalOptionService::class)->getOptionByKey(
            config('space.type_option')
        );
    }

    public function typeOptions(): Collection
    {
        $options = collect();

        $options->push(['id' => null, 'value' => __('None')]);

        foreach ($this->types() as $type) {
            $options->push(['id' => $type->id, 'value' => __($type->name)]);
        }

        return $options;
    }

    public function formattedManagers(Space $space): Collection
    {
        return $space->managers->map(function ($manager) {
            return [
                'id' => $manager->id,
                'value' => $manager->fullName,
            ];
        });
    }

    public function editableRecord(Space $space = null): array
    {
        if (is_null($space)) {
            $space = new Space();
        }

        $spaceData = $space->only(array_merge(
            ['id'],
            $space->getFillable()
        ));

        if (! $space->contacts) {
            $spaceData['contacts'] = [];
        }

        $spaceData['managers'] = $space->managers;

        if ($space->translations->isEmpty()) {
            $space->translateOrNew();
            $spaceData['translations'] = $space->getTranslationsArray();
        } else {
            $spaceData['translations'] = $space->getTranslationsArray();
        }

        return $spaceData;
    }

    public function upload(
        UploadedFile $file,
        string $fileName,
        string $mediaType = null,
    ): Media {
        $folder = ModuleService::mediaFolder();

        $folderPrefix = !app()->environment('production')
            ? config('app.env')
            : null;

        if ($folderPrefix) {
            $folder = $folderPrefix.'_'.$folder;
        }

        $media = $this->mediaService->upload(
            $file,
            $fileName,
            app(MediaStorage::class),
            $folder
        );

        $media->type = $mediaType;
        $media->save();

        return $media;
    }

    private function uploadLogo(UploadedFile $file): Media
    {
        return $this->upload(
            $file,
            'logo_'.Str::random(10),
            ModuleService::MEDIA_TYPE_LOGO
        );
    }

    private function uploadCover(UploadedFile $file): Media
    {
        return $this->upload(
            $file,
            'cover_'.Str::random(10),
            ModuleService::MEDIA_TYPE_COVER
        );
    }

    private function deleteLogoFromStorage(Space $space)
    {
        $media = $space->logo;

        if ($media) {
            $this->mediaService->destroy($media, app(MediaStorage::class));
        }
    }

    private function deleteCoverFromStorage(Space $space)
    {
        $media = $space->cover;

        if ($media) {
            $this->mediaService->destroy($media, app(MediaStorage::class));
        }
    }

    public function replaceLogo(Space $space, UploadedFile $file)
    {
        $media = $this->uploadLogo($file);

        $space->media()->save($media);

        $this->deleteLogoFromStorage($space);

        $space->logo_media_id = $media->id;
        $space->save();
    }

    public function replaceCover(Space $space, UploadedFile $file)
    {
        $media = $this->uploadCover($file);

        $space->media()->save($media);

        $this->deleteCoverFromStorage($space);

        $space->cover_media_id = $media->id;
        $space->save();
    }

    public function deleteLogo(Space $space)
    {
        $this->deleteLogoFromStorage($space);

        $space->logo_media_id = null;
        $space->save();
    }

    public function deleteCover(Space $space)
    {
        $this->deleteCoverFromStorage($space);

        $space->cover_media_id = null;
        $space->save();
    }
}
