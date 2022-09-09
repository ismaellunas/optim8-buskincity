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
        string $term = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $records = Product::orderBy('id', 'DESC')
            ->select(['id', 'status', 'attribute_data'])
            ->when($term, function ($query) use ($term) {
                $query->search($term);
            })
            ->paginate($perPage);

        $this->transformRecords($records);

        return $records;
    }

    public function transformRecords($records)
    {
        $records->getCollection()->transform(function ($record) {
            return (object) [
                'id' => $record->id,
                'name' => $record->translateAttribute('name', config('app.locale')),
                'status' => Str::title($record->status),
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

                $query
                    ->where('key', 'roles')
                    ->whereJsonContains('value', $roleIds);
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
            'short_description' => $product->translateAttribute('description', $locale),
            'status' => $product->status,
            'roles' => $product->roles[0] ?? null,
            'gallery' => $product->gallery->map(fn ($media) => [
                'id' => $media->id,
                'display_file_name' => $media->displayFileName,
                'file_url' => $media->file_url,
            ]),
        ];
    }
}
