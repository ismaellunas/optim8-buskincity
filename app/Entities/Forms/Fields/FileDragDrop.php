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

        $this->maxFileSize = $data['max_file_size'] ?? null;
    }

    public function schema(): array
    {
        $schema = [
            'max_file_size' => $this->maxFileSize,
            'accept' => $this->getMimeTypes(),
        ];

        return array_merge(parent::schema(), $schema);
    }

    private function getMimeTypes(): array
    {
        return MimeType::getMimeTypes($this->getFileExtensions())->all();
    }
}
