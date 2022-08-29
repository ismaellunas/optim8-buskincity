<?php

namespace Modules\Ecommerce\Services;

use App\Contracts\MediaStorageInterface as MediaStorage;
use App\Models\Media;
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

    public function formObject(Product $product)
    {
        return [
            'id' => $product->id,
            'name' => $product->translateAttribute('name', config('app.locale')),
            'description' => $product->translateAttribute('description', config('app.locale')),
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
