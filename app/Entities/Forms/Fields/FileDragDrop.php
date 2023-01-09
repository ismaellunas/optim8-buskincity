<?php

namespace App\Entities\Forms\Fields;

use App\Helpers\MimeType;

class FileDragDrop extends File
{
    protected $type = "FileDragDrop";

    public $maxFileSize;

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->maxFileSize = $data['validation']['rules']['max'] ?? null;

        $this->convertMimesOnValidation();
    }

    public function schema(): array
    {
        $schema = [
            'max_file_size' => $this->maxFileSize,
            'accept' => $this->getMimeTypes(),
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
        $mimes = $this->validation['rules']['mimes'] ?? [];

        $this->validation['rules']['mimes'] = implode(
            ",",
            $this->convertMimesToAcceptedExtensions($mimes)
        );
    }
}
