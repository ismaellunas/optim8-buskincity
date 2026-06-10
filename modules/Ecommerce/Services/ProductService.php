<?php

namespace Modules\Ecommerce\Services;

use App\Contracts\MediaStorageInterface as MediaStorage;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use App\Services\UserScopeService;
use App\Services\UserService;
use Modules\Ecommerce\Entities\Product;
use Modules\Space\Entities\Space;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(private MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function getRecords(
        User $user,
        string $term = null,
        ?array $scopes = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $user->loadMissing(['products', 'spaces', 'adminCities']);

        $productIds = null;

        if ($user->isCityAdministrator() || $user->isSpecialEventsAdmin()) {
            $scopeService = app(UserScopeService::class);
            $cityIds = $scopeService->scopedCityIds($user);
            $cityNames = $scopeService->scopedCityOptions($user)->pluck('name')->all();

            $managedProductIds = $user->products->pluck('id')->all();
            $managedSpaceIds = $user->spaces->pluck('id')->all();

            $citySpaceIds = $cityIds === []
                ? []
                : Space::whereIn('city_id', $cityIds)->pluck('id')->all();

            $allSpaceIds = array_values(array_unique(array_merge($managedSpaceIds, $citySpaceIds)));

            $spaceProductIds = $allSpaceIds === []
                ? []
                : Product::where('productable_type', Space::class)
                    ->whereIn('productable_id', $allSpaceIds)
                    ->pluck('id')
                    ->all();

            $fkProductIds = $cityIds === []
                ? []
                : Product::whereIn('city_id', $cityIds)->pluck('id')->all();

            $metaProductIds = [];

            if ($cityNames !== []) {
                $metaProductIds = Product::whereHas('metas', function ($query) use ($cityNames) {
                    $query->where('key', 'locations')
                        ->where(function ($q) use ($cityNames) {
                            foreach ($cityNames as $cityName) {
                                $q->orWhere(DB::raw("value::json->0->>'city'"), 'ILIKE', $cityName);
                            }
                        });
                })->pluck('id')->all();
            }

            $productIds = array_values(array_unique(array_merge(
                $managedProductIds,
                $spaceProductIds,
                $fkProductIds,
                $metaProductIds
            )));
        } elseif (! $user->can('product.browse')) {
            $productIds = $user->products->pluck('id')->all();
        }

        $records = Product::orderBy('id', 'DESC')
            ->select([
                'id',
                'status',
                'attribute_data',
                'city_id',
                'is_special_event',
                'productable_type',
                'productable_id',
            ])
            ->when($user->isSpecialEventsAdmin(), function ($query) {
                $query->where('is_special_event', true);
            })
            ->when($term, function ($query) use ($term) {
                $query->searchWithoutScout($term);
            })
            ->when($productIds !== null, function ($query) use ($productIds) {
                if ($productIds === []) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('id', $productIds);
                }
            })
            ->when($scopes, function ($query, $scopes) {
                foreach ($scopes as $scopeName => $value) {
                    $query->when($value, function ($query, $value) use ($scopeName) {
                        $query->$scopeName($value);
                    });
                }
            })
            ->paginate($perPage);

        $this->transformRecords($records, $user);

        return $records;
    }

    public function transformRecords($records, User $user)
    {
        $records->getCollection()->transform(function ($record) use ($user) {
            return (object) [
                'id' => $record->id,
                'name' => $record->translateAttribute('name', config('app.locale')),
                'city' => $record->displayCity,
                'country' => $record->displayCountry,
                'status' => Str::title($record->status),
                'can' => [
                    'edit' => $user->can('update', $record),
                    'delete' => $user->can('delete', $record),
                ],
            ];
        });
    }

    public function getFrontendRecords(
        User $user,
        string $term = null,
        ?array $scopes = null,
        int $perPage = 15
    ) {
        $builder = Product::select([
                'id',
                'status',
                'attribute_data'
            ])
            ->when($term, function ($query) use ($term) {
                $query->searchWithoutScout($term);
            })
            ->when($scopes, function ($query, $scopes) {
                foreach ($scopes as $scopeName => $value) {
                    $query->when($value, function ($query, $value) use ($scopeName) {
                        $query->$scopeName($value);
                    });
                }
            })
            ->with(['gallery'])
            ->published();

        if (!($user->isAdministrator || $user->isSuperAdministrator)) {
            $builder->whereHas('metas', function ($query) use ($user) {
                $roleIds = $user->roles->pluck('id');

                if ($roleIds->isNotEmpty()) {
                    $query
                        ->where('key', 'roles')
                        ->whereJsonContains('value', $roleIds);
                } else {
                    $query
                        ->where('key', 'roles')
                        ->whereJsonLength('value', 0);
                }
            });
        }

        $records = $builder->paginate($perPage);

        $this->transformFrontendRecords($records);

        return $records;
    }

    public function transformFrontendRecords($records)
    {
        $records->getCollection()->transform(function ($record) {
            return (object) [
                'id' => $record->id,
                'name' => $record->translateAttribute('name', config('app.locale')),
                'status' => Str::title($record->status),
                'city' => $record->displayCity,
                'country' => $record->displayCountry,
                'coverUrl' => $record->getCoverThumbnailUrl(),
            ];
        });
    }

    public function roleOptions(): Collection
    {
        return collect()
            ->push(['id' => null, 'value' => '- '.__('Select').' -'])
            ->merge(app(UserService::class)->getRoleOptions());
    }

    public function upload(
        Product $product,
        UploadedFile $file,
    ): Media {
        $fileName = preg_replace(
            '/[^a-z0-9]+/',
            '-',
            Str::lower(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
        );

        $media = $this->mediaService->upload(
            $file,
            $fileName,
            app(MediaStorage::class),
        );

        $media->save();

        $product->gallery()->save($media);

        return $media;
    }

    public function statusOptions(): Collection
    {
        return collect([
            ['id' => Product::STATUS_DRAFT, 'value' => Str::title(Product::STATUS_DRAFT)],
            ['id' => Product::STATUS_PUBLISHED, 'value' => Str::title(Product::STATUS_PUBLISHED)]
        ]);
    }

    public function formResource(Product $product): array
    {
        $locale = config('app.locale');

        return [
            'id' => $product->id,
            'name' => $product->translateAttribute('name', $locale),
            'description' => $product->translateAttribute('description', $locale),
            'short_description' => $product->translateAttribute('short_description', $locale),
            'status' => $product->status,
            'roles' => $product->roles[0] ?? null,
            'is_check_in_required' => (bool) $product->is_check_in_required ?? false,
            'location' => $product->locations[0] ?? null,
            'gallery' => $product->gallery->map(fn ($media) => [
                'id' => $media->id,
                'display_file_name' => $media->displayFileName,
                'file_url' => $media->file_url,
                'is_image' => $media->isImage,
                'thumbnail_url' => $media->thumbnailUrl,
                'file_name_without_extension' => $media->fileNameWithoutExtension,
                'can_edit_existing_media' => auth()->user()->can('update', $media),
                'translations' => $media->translations,
            ]),
        ];
    }

    public function productDetailResource(Product $product): array
    {
        $locale = config('app.locale');

        $resource = [
            'id' => $product->id,
            'sku' => $product->variants->first()->sku,
            'name' => $product->translateAttribute('name', $locale),
            'description' => $product->translateAttribute('description', $locale),
            'short_description' => $product->translateAttribute('short_description', $locale),
            'gallery' => $product->gallery->map(fn ($media) => [
                'id' => $media->id,
                'display_file_name' => $media->displayFileName,
                'file_url' => $media->getOptimizedImageUrl(900, 600),
                'thumbnail_url' => $media->getThumbnailUrl(300, 200),
            ]),
        ];

        return $resource;
    }

    public function managers(
        string $term = null,
        array $excludedIds = [],
        int $limit = 15,
    ): Collection {

        return User::available()
            ->backend()
            ->notInRoleNames([
                config('permission.role_names.super_admin'),
                config('permission.role_names.admin'),
            ])
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

    public function formattedManagers(Product $product): Collection
    {
        return $product->managers->map(function ($manager) {
            return [
                'id' => $manager->id,
                'value' => $manager->fullName,
            ];
        });
    }
}
