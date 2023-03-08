<?php

namespace Modules\FormBuilder\Fields;

use App\Entities\CloudinaryStorage;
use App\Entities\Forms\Fields\FileDragDrop as AppFileDragDrop;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Modules\FormBuilder\Entities\Media;
use Modules\FormBuilder\Services\MediaService;
use Mews\Purifier\Facades\Purifier;

class FileDragDrop extends BaseField
{
    public function getSavedData(mixed $value): mixed
    {
        $mediaId = [];
        $files = $value['files'] ?? [];

        if (!empty($files)) {
            $media = $this->uploadFiles($files);

            $mediaId = $media->pluck('id')->toArray();
        }

        return [
            'mediaId' => $mediaId,
        ];
    }

    public function value(): mixed
    {
        $mediaIds = $this->value['mediaId'] ?? [];

        if (empty($mediaIds)) {
            return "-";
        }

        $media = $this->getMedia($mediaIds);

        $html = "<ul>";

        foreach ($media as $medium) {
            $html .= "<li><a href=".$medium->file_url." target='_blank'>{$medium->displayFileName}</a></li>";
        }

        $html .= "</ul>";

        return $html;
    }

    public function componentValue(): array
    {
        $mediaIds = $this->value['mediaId'] ?? [];

        if (empty($mediaIds)) {
            return [];
        }

        $media = $this->getMedia($mediaIds);
        $media = $media->transform(function ($medium) {
            $medium->thumbnail_url = $medium->thumbnailUrl;
            $medium->file_name_without_extension = $medium->fileNameWithoutExtension;
            $medium->is_image = $medium->isImage;
            $medium->display_file_name = $medium->displayFileName;

            return $medium;
        });

        return [
            'component' => 'MediaGallery',
            'value' => $media,
        ];
    }

    private function uploadFiles(array $files): Collection
    {
        $media = collect();

        foreach ($files as $file) {
            $media->push(app(MediaService::class)->uploadField(
                $file,
                new CloudinaryStorage(),
            ));
        }

        return $media;
    }

    private function getMedia(array $mediaIds): Collection
    {
        return Media::select([
                'file_url',
                'file_name',
                'extension',
                'file_type',
                'version',
            ])
            ->with([
                'translations' => function ($q) {
                    $q->select(['id', 'media_id', 'alt', 'description', 'locale']);
                },
            ])
            ->whereIn('id', $mediaIds)
            ->get();
    }
}
