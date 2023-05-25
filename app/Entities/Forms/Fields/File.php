<?php

namespace App\Entities\Forms\Fields;

use App\Entities\CloudinaryStorage;
use App\Helpers\HumanReadable;
use App\Models\Media;
use App\Rules\FieldMaxFile;
use App\Rules\FieldMinFile;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class File extends BaseField
{
    const TYPE = "File";

    protected $type = "File";

    public $fileLabel;
    public $placeholder;
    public $maxFileNumber;
    public $minFileNumber;

    public $defaultValue = [];

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->placeholder = $data['placeholder'] ?? null;

        $this->maxFileNumber = $data['max_file_number'] ?? null;
        $this->minFileNumber = $data['min_file_number'] ?? 0;
        $this->fileLabel = $data['file_label'] ?? 'Choose a file';

        $this->checkValidationMaxFileSize();
    }

    public function schema(): array
    {
        $schema = [
            'file_label' => $this->fileLabel,
            'placeholder' => $this->placeholder,
            'max_file_number' => $this->maxFileNumber,
            'min_file_number' => $this->minFileNumber,
            'media' => (
                !empty($this->storedValue)
                ? $this->getMedias($this->storedValue)
                : $this->defaultValue
            ),
            'accept' => $this->getDottedFileExtensions(),
        ];

        return array_merge(parent::schema(), $schema);
    }

    public function getLabels(array $inputs = []): array
    {
        $fileKeys[$this->filesKey()] = $this->label;

        if (
            !empty($inputs[$this->name])
            && !empty($inputs[$this->name]['files'])
        ) {
            foreach (array_keys($inputs[$this->name]['files']) as $key) {
                $fileKeys[$this->name.'.files.'.$key] = $this->label;
            }
        }

        return array_merge(
            parent::getLabels($inputs),
            $fileKeys
        );
    }

    private function uploadFiles($files): Collection
    {
        $media = collect();

        foreach ($files as $file) {
            $media->push(app(MediaService::class)->uploadUserMeta(
                $file,
                new CloudinaryStorage(),
                $this->entity,
            ));
        }

        return $media;
    }

    private function deleteFiles($mediaIds)
    {
        $mediaService = app(MediaService::class);

        $media = Media::whereIn('id', $mediaIds)->get();

        foreach ($media as $medium) {
            $mediaService->destroy(
                $medium,
                new CloudinaryStorage()
            );
        }
    }

    public function getDataToBeSaved(array $inputs): array
    {
        $data = [];
        $value = [];

        if (! isset($inputs[$this->name])) {
            return $data;
        }

        $storedValue = $this->storedValue ?? [];

        $files = $inputs[$this->name]['files'] ?? [];

        $deleteMediaIds = $inputs[$this->name]['deleted_media'] ?? [];

        if (! empty($deleteMediaIds)) {
            $deleteMediaIds = array_intersect(
                $deleteMediaIds,
                $storedValue
            );

            $this->deleteFiles($deleteMediaIds);

            $value = array_diff(
                $storedValue,
                $deleteMediaIds,
            );

        } else {
            $value = $storedValue;
        }

        if (! empty($files)) {

            $media = $this->uploadFiles($files);

            $value = array_merge(
                $value,
                $media->pluck('id')->all()
            );
        }

        $data[$this->name] = array_values($value);

        return $data;
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

    private function filesKey(): string
    {
        return $this->name.'.files';
    }

    private function deleteMediaKey(): string
    {
        return $this->name.'.deleted_media';
    }

    protected function getFileExtensions(): array
    {
        $rules = collect($this->validation['rules'] ?? []);

        $extensions = [];

        $mimes = $rules['mimes'] ?? "";

        if ($mimes) {
            $mimes = preg_replace('/\s+/', '', $mimes);
            $extensions = explode(',', $mimes);
        }

        return $extensions;
    }

    private function getDottedFileExtensions(): array
    {
        return array_map(
            function ($value) { return '.'.$value;},
            $this->getFileExtensions()
        );
    }

    public function validationRules(): array
    {
        $rules[$this->name] = [];
        $rules[$this->filesKey()] = [];
        $rules[$this->deleteMediaKey()] = [];

        $definedRules = $this->validation['rules'] ?? [];

        if ($this->isRequired() && $this->minFileNumber < 1) {
            $this->minFileNumber = 1;
        }

        $rules[$this->filesKey()][] = new FieldMinFile(
            $this->minFileNumber,
            $this->storedValue
        );

        $rules[$this->name.'.files'][] = new FieldMaxFile(
            $this->maxFileNumber,
            $this->storedValue
        );

        $rules[$this->name.'.files.*'] = $definedRules;

        $this->transformToFlatten($rules);

        $rules[$this->name.'.files.*'][] = 'file';

        return $rules;
    }

    public function getInstructions(): array
    {
        $instructions = collect();

        $mimes = $this->getFileExtensions();

        if (! empty($mimes)) {
            $instructions->push('Accepted file extensions: '.implode(', ', $mimes));
        }

        $rules = $this->validation['rules'] ?? [];
        $additionalInstructions = [];

        if (
            array_key_exists('max', $rules)
            && ! empty($rules['max'])
        ) {
            $bytes = $rules['max'];

            $additionalInstructions[] = 'Max file size: '. HumanReadable::bytesToHuman($bytes * 1024);
        }

        return $instructions
            ->merge($additionalInstructions)
            ->filter()
            ->values()
            ->all();
    }

    protected function getSchemaValue(): mixed
    {
        return [
            'deleted_media' => [],
            'files' => [],
        ];
    }

    public function setMedially(Model $relatedEntity, array $mediaIds = [])
    {
        $media = Media::whereIn('id', $mediaIds)
            ->whereNull('medially_id')
            ->get();

        foreach ($media as $medium) {
            $medium->medially()->associate($relatedEntity);
            $medium->save();
        }
    }

    private function checkValidationMaxFileSize(): void
    {
        $maxFileSize = MediaService::maxFileSize();

        if (
            array_key_exists('max', $this->validation['rules'])
            && (int)$this->validation['rules']['max'] > $maxFileSize
        ) {
            $this->validation['rules']['max'] = $maxFileSize;
        }
    }
}
