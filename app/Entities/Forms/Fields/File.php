<?php

namespace App\Entities\Forms\Fields;

use App\Entities\CloudinaryStorage;
use App\Helpers\HumanReadable;
use App\Models\{
    Media,
    User,
};
use App\Rules\FieldMaxFile;
use App\Rules\FieldMinFile;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class File extends BaseField
{
    protected $type = "File";

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
    }

    public function schema(): array
    {
        $schema = [
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
                (!App::environment('production') ? 'local_' : null)
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

        if (! $inputs[$this->name]) {
            return $data;
        }

        $storedValue = $this->storedValue ?? [];

        $files = $inputs[$this->name]['files'] ?? [];

        $deleteMediaIds = $inputs[$this->name]['delete_media'] ?? [];

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

    private function filesKey(): string
    {
        return $this->name.'.files';
    }

    private function deleteMediaKey(): string
    {
        return $this->name.'.delete_media';
    }

    private function getFileExtensions(): array
    {
        $rules = collect($this->validation['rules'] ?? []);

        $extensions = [];

        $mimes = $rules->first(function($rule) {
            return is_string($rule) && Str::startsWith($rule, 'mimes:');
        });

        if ($mimes) {
            $mimes = preg_replace('/\s+/', '', Str::after($mimes, 'mimes:'));
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

    public function validationRules(User $entity = null): array
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

        return $rules;
    }

    public function getInstructions(): array
    {
        $instructions = collect();

        $rules = collect($this->validation['rules'] ?? []);

        $mimes = $this->getFileExtensions();

        if (! empty($mimes)) {
            $instructions->push('Accepted file extensions: '.implode(', ', $mimes));
        }

        $additionalInstructions = $rules->map(function($rule) {
            if (is_string($rule)) {
                if (Str::startsWith($rule, 'max:')) {
                    $bytes = preg_replace('/\s+/', '', Str::after($rule, 'max:'));

                    return 'Max file size: '. HumanReadable::bytesToHuman($bytes*1024);
                }
            }

            return null;
        });

        return $instructions
            ->merge($additionalInstructions)
            ->filter()
            ->values()
            ->all();
    }

    protected function getSchemaValue(): mixed
    {
        return [
            'delete_media' => [],
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
}
