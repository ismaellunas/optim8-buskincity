<?php

namespace Modules\Ecommerce\Services;

use App\Contracts\MediaStorageInterface as MediaStorage;
use App\Models\Media;
use App\Services\MediaService;
use App\Services\UserService;
use GetCandy\Models\Product;
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
            $record->id = $record->id;
            $record->name = $record->translateAttribute('name');
            $record->status = $record->status;

            return $record;
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
}
