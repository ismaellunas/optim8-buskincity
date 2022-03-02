<?php

namespace App\View\Components\Form\Fields;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Support\Collection;

class File extends BaseField
{
    public $media;

    public function __construct($label, $value)
    {
        parent::__construct($label, $value);

        $this->media = (
            !empty($this->value)
            ? $this->getMedias($this->value)
            : $this->value
        );
    }

    private function getMedias($mediaIds): Collection
    {
        $media = Media::select([
            'id',
            'extension',
            'file_name',
            'file_type',
            'file_url',
            'size',
            'type',
            'updated_at',
        ])
            ->whereIn('id', $mediaIds)
            ->get();

        $media->transform([app(MediaService::class), 'transformRecord']);

        return $media;
    }
}
