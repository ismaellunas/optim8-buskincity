<?php

namespace Modules\Ecommerce\Services;

use App\Contracts\MediaStorageInterface as MediaStorage;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use App\Services\UserService;
use Modules\Ecommerce\Entities\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Ecommerce\ModuleService;

class ProductService
{
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function getRecords(
        User $user,
        string $term = null,
        ?array $scopes = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $productIds = null;

        if (!$user->can('product.browse')) {
            $productIds = $user->products->pluck('id');
        }

        $records = Product::orderBy('id', 'DESC')
            ->select(['id', 'status', 'attribute_data'])
            ->when($term, function ($query) use ($term) {
                $query->searchWithoutScout($term);
            })
            ->when($productIds, function ($query, $productIds) {
                $query->whereIn('id', $productIds);
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
        int $perPage = 15
    ) {
        $builder = Product::orderBy('id', 'DESC')
            ->select(['id', 'status', 'attribute_data'])
            ->when($term, function ($query) use ($term) {
                $query->searchWithoutScout($term);
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
                'coverUrl' => $record->coverThumbnailUrl,
            ];
        });
    }

    public function roleOptions(): Collection
    {
        return collect(array_merge(
            [['id' => null, 'value' => '- '.__('Select').' -']],
            app(UserService::class)->getRoleOptions()
        ));
    }

    public function upload(
        Product $product,
        UploadedFile $file,
        string $mediaType = null,
    ): Media {
        $folder = ModuleService::productMediaFolder().'/'.$product->id;

        $folderPrefix = !app()->environment('production')
            ? config('app.env')
            : null;

        if ($folderPrefix) {
            $folder = $folderPrefix.'_'.$folder;
        }

        $fileName = preg_replace(
            '/[^a-z0-9]+/',
            '-',
            Str::lower(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
        );

        $media = $this->mediaService->upload(
            $file,
            $fileName,
            app(MediaStorage::class),
            $folder
        );

        $media->type = $mediaType;
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
            'gallery' => $product->gallery->map(fn ($media) => [
                'id' => $media->id,
                'display_file_name' => $media->displayFileName,
                'file_url' => $media->file_url,
                'is_image' => $media->isImage,
                'thumbnail_url' => $media->thumbnailUrl,
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
                'file_url' => $media->file_url,
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
