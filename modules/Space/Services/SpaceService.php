<?php

namespace Modules\Space\Services;

use App\Contracts\MediaStorageInterface as MediaStorage;
use App\Models\Media;
use App\Models\User;
use App\Services\GlobalOptionService;
use App\Services\MediaService;
use App\Services\MenuService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\Collection as NestedSetCollection;
use Modules\Space\Entities\Page;
use Modules\Space\Entities\Space;
use Modules\Space\ModuleService;

class SpaceService
{
    private $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function getRecords(
        Authenticatable $user,
        ?array $ids = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        Space::disableAutoloadTranslations();

        $columnNames = ['id', 'name', 'parent_id', 'type_id', '_lft', '_rgt'];

        $spaces = null;

        if ($ids) {
            $spaces = Space::select('id', '_lft', '_rgt')->whereIn('id', $ids)->get();
        }

        $records = Space::select($columnNames)
            ->withDepth()
            ->with([
                'ancestors' => function ($query) use ($columnNames) {
                    $query->select($columnNames);
                    $query->defaultOrder();
                    $query->withDepth();
                    $query->with('type:id,name');
                },
                'type:id,name',
            ])
            ->when($spaces, function ($query, $spaces) {
                foreach ($spaces as $key => $space) {
                    $boolean = $key == 0 ? 'and' : 'or';
                    $query->whereDescendantOrSelf($space, $boolean);
                }
            })
            ->defaultOrder()
            ->paginate($perPage);

        $records->getCollection()->transform(function ($space) use ($user) {
            $space->makeHidden('type');

            $space->ancestorNames = $space
                   ->ancestors
                   ->sortBy('depth')
                   ->pluck('name')
                   ->all();

            $space->typeName = $space->type->name ?? null;
            $space->can = [
                'edit' => $user->can('edit', $space),
            ];

            return $space;
        });

        return $records;
    }

    public function parentOptions(
        ?array $ids = null,
        string $label = null
    ): Collection {
        $spaces = null;

        if ($ids) {
            $spaces = Space::select('id', '_lft', '_rgt')
                ->whereIn('id', $ids)
                ->get();
        }

        $options = Space::select(['id', 'name', 'parent_id', '_lft', '_rgt'])
            ->withDepth()
            ->with('ancestors', function ($query) {
                $query->select(['id', 'name', 'parent_id', '_lft', '_rgt']);
                $query->withDepth();
            })
            ->when($spaces, function ($query, $spaces) {
                foreach ($spaces as $key => $space) {
                    $boolean = $key == 0 ? 'and' : 'or';
                    $query->whereDescendantOrSelf($space, $boolean);
                }
            })
            ->hasChildren()
            ->get()
            ->toFlatTree()
            ->filter(fn ($space) => $space->depth < 2)
            ->map(function ($space) {
                return [
                    'id' => $space->id,
                    'value' => $space->name,
                    'depth' => $space->depth,
                ];
            })
            ->sortBy([
                ['depth', 'asc'],
                ['value', 'asc']
            ])
            ->values();

        if ($label) {
            $options->prepend(['id' => null, 'value' => $label]);
        }

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
        return app(GlobalOptionService::class)->getOptionByType(
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

    public function removeAllMedia(array $spaces): void
    {
        foreach ($spaces as $space) {
            $this->deleteLogoFromStorage($space);
            $this->deleteCoverFromStorage($space);
        }
    }

    public function removeAllPages(array $spaces): void
    {
        foreach ($spaces as $space) {
            if ($space->page) {
                $space->page->delete();
            }
        }
    }

    public function removeAllMenus(array $spaces): void
    {
        foreach ($spaces as $space) {
            app(MenuService::class)->removeModelFromMenus($space);
        }
    }

    public function getTopParents(): NestedSetCollection
    {
        return Space::topParent()
            ->select([
                'id',
                'name',
                'logo_media_id',
                'cover_media_id',
                'page_id',
                'parent_id',
                'is_page_enabled',
            ])
            ->withStructuredUrl([currentLocale(), defaultLocale()])
            ->with(['translations'])
            ->orderBy('name', 'asc')
            ->get();
    }

    public function pageFormRecord(Space $space): Page
    {
        $page = $space->page;

        $page->translations->transform(function ($translation) {

            $translation->append('landingPageSpaceUrl');

            $translation->setVisible([
                'id',
                'locale',
                'title',
                'excerpt',
                'slug',
                'meta_title',
                'meta_description',
                'status',
                'landingPageSpaceUrl',
            ]);

            return $translation;
        });

        return $page;
    }
}
