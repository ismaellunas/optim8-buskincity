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
        array $scopes = null,
        int $perPage = 15
    ): LengthAwarePaginator {
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
            ->when($scopes, function ($query, $scopes) {
                foreach ($scopes as $scopeName => $value) {
                    $query->$scopeName($value);
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
                'delete' => $user->can('delete', $space)
            ];

            return $space;
        });

        return $records;
    }

    public function parentOptions(
        ?Collection $managedSpaces = null,
        string $label = null,
        ?array $ignoreIds = null,
    ): Collection {
        $builder = Space::select(['id', 'name', 'parent_id', '_lft', '_rgt'])
            ->withDepth()
            ->when($ignoreIds, function ($query, $ignoreIds) {
                $query->whereNotIn('id', $ignoreIds);
            });

        if (is_countable($managedSpaces)) {
            if ($managedSpaces->isEmpty()) {
                $builder->whereRaw('1 = 0');
            } else {
                foreach ($managedSpaces as $key => $id) {
                    $boolean = $key == 0 ? 'and' : 'or';
                    $builder->whereDescendantOrSelf($id, $boolean);
                }
            }
        }

        $options = $builder->get()
            ->filter(fn ($space) => $space->isParentable)
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
            $options->prepend(['id' => null, 'value' => $label, 'depth' => -1]);
        }

        return $options;
    }

    public function parentOptionsFor(
        Space $space,
        ?Collection $managedSpaces = null,
        ?string $label = null
    ): Collection {
        if (is_null($space->depth)) {
            $space = Space::withDepth()->find($space->id);
        }

        $leafDepth = $space->descendants()
            ->whereIsLeaf()
            ->withDepth()
            ->get(['id', 'parent_id', '_lft', '_rgt'])
            ->max('depth') ?? $space->depth;

        $maxAvailableDepth = ModuleService::maxParentDepth() - ($leafDepth - $space->depth + 1);

        $options = $this->parentOptions(
            $managedSpaces,
            $label,
            $space->descendants->pluck('id')->push($space->id)->all()
        );

        return $options->filter(function ($option) use ($maxAvailableDepth, $space) {
            return (
                $option['id'] == $space->parent_id
                || $option['depth'] <= $maxAvailableDepth
            );
        });
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

    public function typeOptions(string $noneLabel = null): Collection
    {
        $options = collect();

        if (!is_null($noneLabel)) {
            $options->push(['id' => null, 'value' => $noneLabel]);
        }

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
    ): Media {

        $media = $this->mediaService->upload(
            $file,
            $fileName,
            app(MediaStorage::class),
        );

        $media->save();

        return $media;
    }

    private function uploadLogo(UploadedFile $file): Media
    {
        return $this->upload(
            $file,
            'logo_'.Str::random(10),
        );
    }

    private function uploadCover(UploadedFile $file): Media
    {
        return $this->upload(
            $file,
            'cover_'.Str::random(10),
        );
    }

    private function detachLogo(Space $space)
    {
        $logoMediaId = $space->logo_media_id;

        if ($logoMediaId) {
            $space->detachMedia($logoMediaId);
        }
    }

    private function detchCover(Space $space)
    {
        $coverMediaId = $space->cover_media_id;

        if ($coverMediaId) {
            $space->detachMedia($coverMediaId);
        }
    }

    public function replaceLogo(Space $space, UploadedFile $file)
    {
        $media = $this->uploadLogo($file);

        $space->media()->save($media);

        $this->detachLogo($space);

        $space->logo_media_id = $media->id;
        $space->save();
    }

    public function replaceCover(Space $space, UploadedFile $file)
    {
        $media = $this->uploadCover($file);

        $space->media()->save($media);

        $this->detchCover($space);

        $space->cover_media_id = $media->id;
        $space->save();
    }

    public function deleteLogo(Space $space)
    {
        $this->detachLogo($space);

        $space->logo_media_id = null;
        $space->save();
    }

    public function deleteCover(Space $space)
    {
        $this->detchCover($space);

        $space->cover_media_id = null;
        $space->save();
    }

    public function removeAllMedia(array $spaces): void
    {
        foreach ($spaces as $space) {
            $this->detachLogo($space);
            $this->detchCover($space);
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
