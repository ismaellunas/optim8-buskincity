<?php

namespace App\Entities\Forms\Fields;

use App\Contracts\MutatedValueFieldInterface;
use App\Entities\CloudinaryStorage;
use App\Helpers\HumanReadable;
use App\Models\Media;
use App\Rules\FieldMaxFile;
use App\Services\MediaService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class File extends BaseField implements MutatedValueFieldInterface
{
    protected $type = "File";

    public $placeholder;
    public $maxFileNumber;
    public $minFileNumber;

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->placeholder = $data['placeholder'] ?? null;

        $this->maxFileNumber = $data['max_file_number'] ?? null;
        $this->minFileNumber = $data['min_file_number'] ?? 0;
    }

    public function schema(): array
    {
        $schema = [
            'placeholder' => $this->placeholder,
            'max_file_number' => $this->maxFileNumber,
            'min_file_number' => $this->minFileNumber,
        ];

        return array_merge(parent::schema(), $schema);
    }

    public function getLabels(array $inputs = []): array
    {
        $fileKeys = [];

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
                new CloudinaryStorage()
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

    public function getDataToBeSaved(array $inputs, $oldValues = null): array
    {
        $data = [];
        $value = [];

        if (! $inputs[$this->name]) {
            return $data;
        }

        $oldValue = [];

        if ($oldValues && $oldValues->has($this->name)) {
            $oldValue = $oldValues->get($this->name, []);
            $value = $oldValue;
        }

        $files = $inputs[$this->name]['files'] ?? [];

        $deleteMediaIds = $inputs[$this->name]['delete_media'] ?? [];

        if (! empty($deleteMediaIds)) {
            $deleteMediaIds = array_intersect(
                $deleteMediaIds,
                $oldValue
            );

            $this->deleteFiles($deleteMediaIds);

            $value = array_diff(
                $value,
                $deleteMediaIds,
            );
        }

        if (! empty($files)) {

            $files = $inputs[$this->name]['files'];

            $media = $this->uploadFiles($files);

            $value = array_merge(
                $value,
                $media->pluck('id')->all()
            );
        }

        $data[$this->name] = array_values($value);

        return $data;
    }

    private function getMedias($mediaIds)
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

    public function mutateValue(mixed $value): mixed
    {
        return [
            'delete_media' => [],
            'files' => [],
        ];
    }

    public function schemaBasedOnValue($value): array
    {
        return [
            'media' => $this->getMedias($value)->all(),
        ];
    }

    private function filesKey(): string
    {
        return $this->name.'.files';
    }

    private function deleteMediaKey(): string
    {
        return $this->name.'.delete_media';
    }

    public function validationRules($oldValues = null): array
    {
        $rules[$this->name] = [];
        $rules[$this->filesKey()] = [];
        $rules[$this->deleteMediaKey()] = [];

        $definedRules = $this->validation['rules'] ?? [];

        $oldValue = $oldValues->get($this->name, []);

        $existingFileNumber = count($oldValue);

        if ($this->isRequired()) {

            if ($existingFileNumber < 1 && $this->minFileNumber < 1) {

                $rules[$this->name][] = 'required';

                $rules[$this->name.'.files'][] = 'min:1';
            }
        }

        /*
        if ($this->minFileNumber > 0) {
            $minFileNumber = $this->minFileNumber - $existingFileNumber;

            if ($minFileNumber > 0) {
                $rules[$this->name.'.files'][] = 'min:'.$minFileNumber;
            }
        }
        */

        $rules[$this->name.'.files'][] = new FieldMaxFile(
            $this->maxFileNumber,
            $oldValue
        );

        $rules[$this->name.'.files.*'] = $definedRules;

        return $rules;
    }

    public function getInstructions(): array
    {
        $rules = collect($this->validation['rules'] ?? []);

        $instructions = $rules->map(function($rule) {
            if (is_string($rule)) {
                if (Str::startsWith($rule, 'mimes:')) {
                    $mimes = preg_replace('/\s+/', '', Str::after($rule, 'mimes:'));
                    $mimes = explode(',', $mimes);

                    if (!empty($mimes)) {
                        return 'Accepted file extensions: '.implode(', ', $mimes);
                    }
                }

                if (Str::startsWith($rule, 'max:')) {
                    $bytes = preg_replace('/\s+/', '', Str::after($rule, 'max:'));

                    return 'Max file size: '. HumanReadable::bytesToHuman($bytes*1024);
                }
            }

            return null;

        });

        return $instructions->filter()->values()->all();
    }
}
