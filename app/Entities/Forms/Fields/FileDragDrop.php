<?php

namespace App\Entities\Forms\Fields;

use App\Helpers\MimeType;
use Illuminate\Support\Arr;

class FileDragDrop extends File
{
    const TYPE = "FileDragDrop";

    protected $type = "FileDragDrop";

    public $maxFileSize;

    private $mimes = [];

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->maxFileSize = $data['validation']['rules']['max'] ?? null;
        $this->mimes = $data['validation']['rules']['mimes'] ?? [];

        $this->convertMimesOnValidation();

        if (! Arr::get($data, 'is_multiple_upload', false)) {
            $this->maxFileNumber = 1;
        }
    }

    public function schema(): array
    {
        $schema = [
            'max_file_size' => $this->maxFileSize,
            'accept' => $this->getMimeTypes(),
            'is_image_editor_enabled' => $this->isImageEditorEnabled(),
            'dimensions' => $this->getDimensions(),
        ];

        return array_merge(parent::schema(), $schema);
    }

    private function convertMimesToAcceptedExtensions(array $mimes): array
    {
        $extensions = config('constants.extensions');
        $acceptedExtensions = [];

        foreach ($mimes as $type) {
            $acceptedExtensions = [
                ...$acceptedExtensions,
                ...$extensions[$type] ?? []
            ];
        }

        return $acceptedExtensions;
    }

    private function getMimeTypes(): array
    {
        return MimeType::getMimeTypes($this->getFileExtensions())->all();
    }

    private function convertMimesOnValidation(): void
    {
        $mimes = $this->mimes;

        $this->validation['rules']['mimes'] = implode(
            ",",
            $this->convertMimesToAcceptedExtensions($mimes)
        );
    }

    private function isImageEditorEnabled(): bool
    {
        return in_array('image', $this->mimes)
            && $this->data['is_image_editor_enabled'] ?? false;
    }

    private function getDimensions(): array
    {
        if ($this->isImageEditorEnabled()) {
            return $this->data['image_dimensions'] ?? [];
        }

        return [];
    }
}
