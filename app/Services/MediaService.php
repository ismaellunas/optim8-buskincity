<?php

namespace App\Services;

use App\Contracts\MediaStorageInterface as MediaStorage;
use App\Entities\MediaAsset;
use App\Models\{
    Media,
    User
};
use Astrotomic\Translatable\Validation\RuleFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

class MediaService
{
    public static function isFileNameExists(
        string $fileName,
        array $excludedIds = []
    ): bool {
        $queryBuilder = Media::whereRaw('LOWER(file_name) = LOWER(?)', [$fileName]);

        if (!empty($excludedIds)) {
            $queryBuilder->whereIn('id', $excludedIds);
        }

        return $queryBuilder->exists();
    }

    public static function getUniqueFileName(
        string $fileName,
        array $excludedIds = [],
        string $extension = null,
        string $folder = null
    ): string {
        $searchFileName = $fileName;

        if (!empty($folder)) {
            $searchFileName = $folder.'/'.$searchFileName;
        }

        if (!empty($extension)) {
            $searchFileName .= '.'.$extension;
        }

        if (self::isFileNameExists($searchFileName, $excludedIds)) {
            return self::getUniqueFileName(
                $fileName.'-'.Str::lower(Str::random(6)),
                [],
                $extension
            );
        }
        return $fileName.($extension ? '.'.$extension : '');
    }

    public static function getTranslationRules(): array
    {
        return RuleFactory::make([
            'translations.%alt%' => 'sometimes|nullable|string|max:255',
            'translations.%description%' => 'sometimes|nullable|string|max:500',
        ]);
    }

    public function getRecords(
        string $term = null,
        ?array $scopeNames = null,
        int $recordsPerPage = 12
    ) {
        $user = auth()->user();
        $hasAccessToOtherMedia = $user->hasAccessToOtherMedia;

        $query = Media::orderBy('id', 'DESC')
            ->when(!$hasAccessToOtherMedia, function (Builder $query) use ($user) {
                $query->uploader($user->id);
            })
            ->when($term, function (Builder $query, $term) {
                $query->where('file_name', 'ILIKE', '%'.$term.'%');
                $query->orWhereHas('translations', function (Builder $query) use ($term) {
                    $query->where('alt', 'ILIKE', '%'.$term.'%');
                    $query->orWhere('description', 'ILIKE', '%'.$term.'%');
                });
            })
            ->with([
                'translations' => function ($q) {
                    $q->select(['id', 'media_id', 'alt', 'description', 'locale']);
                },
            ])
            ->default();

        if ($scopeNames) {
            $query->where(function ($query) use ($scopeNames) {
                foreach ($scopeNames as $scopeName) {
                    $query->orWhere(function ($query) use ($scopeName) {
                        $query->{$scopeName}();
                    });
                }
            });
        }

        $records = $query->paginate($recordsPerPage);

        $this->transformRecords($records);

        return $records;
    }

    public function transformRecords($records)
    {
        $records->transform([$this, 'transformRecord']);
    }

    public function transformRecord($record)
    {
        $record->thumbnail_url = $record->thumbnailUrl;
        $record->file_name_without_extension = $record->fileNameWithoutExtension;
        $record->is_image = $record->isImage;
        $record->readable_size = $record->readableSize;
        $record->date_modified = $record->updated_at->format('d/m/Y H:m');
        $record->display_file_name = $record->displayFileName;
        $record->canDeleted = $record->canDeleted;

        return $record;
    }

    protected function fillMediaWithMediaAsset(
        Media &$media,
        MediaAsset $asset
    ) {
        $media->assets = $asset->assets;
        $media->extension = $asset->extension;
        $media->file_name = $asset->fileName;
        $media->file_type = $asset->fileType;
        $media->file_url = $asset->fileUrl;
        $media->size = $asset->size;
        $media->version = $asset->version;
    }

    protected function isOriginalExtensionNeeded(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();

        return !(
            Str::startsWith($mimeType, 'image/')
            || Str::startsWith($mimeType, 'video/')
            || $file->getClientOriginalExtension() == 'pdf'
        );
    }

    public function upload(
        UploadedFile $file,
        string $fileName,
        MediaStorage $mediaStorage,
        string $folder = null
    ): Media {
        $media = new Media();

        $extension = null;

        $clientExtension = $file->getClientOriginalExtension();

        if ($this->isOriginalExtensionNeeded($file)) {
            $extension = $clientExtension;
        }

        $fileName = MediaService::getUniqueFileName(
            Str::lower($fileName),
            [],
            $extension,
            $folder
        );

        $params = [$file, $fileName, $extension];

        if (! is_null($folder)) {
            $folder = $this->getFolderPrefix().$folder;

            array_push($params, $folder);
        }

        $this->fillMediaWithMediaAsset(
            $media,
            call_user_func_array(
                [$mediaStorage, 'upload'],
                $params
            )
        );
        $media->save();
        $media->saveUserId(auth()->user()->id);

        return $media;
    }

    public function uploadProfile(
        UploadedFile $file,
        string $fileName,
        MediaStorage $mediaStorage,
        string $folder = null,
    ): Media {
        $media = new Media();

        $extension = null;

        $clientExtension = $file->getClientOriginalExtension();

        if ($this->isOriginalExtensionNeeded($file)) {
            $extension = $clientExtension;
        }

        $fileName = MediaService::getUniqueFileName(
            Str::lower($fileName),
            [],
            $extension,
            $folder
        );

        $params = [$file, $fileName, $extension];

        if (! is_null($folder)) {
            $folder = $this->getFolderPrefix().$folder;

            array_push($params, $folder);
        }

        $this->fillMediaWithMediaAsset(
            $media,
            call_user_func_array(
                [$mediaStorage, 'upload'],
                $params
            )
        );

        $media->type = Media::TYPE_PROFILE;
        $media->save();
        $media->saveUserId(auth()->user()->id);

        return $media;
    }

    public function duplicateImage(
        UploadedFile $file,
        Media $media,
        MediaStorage $mediaStorage
    ): Media {
        $replicatedMedia = $media->replicate();

        $this->fillMediaWithMediaAsset(
            $replicatedMedia,
            $mediaStorage->upload(
                $file,
                MediaService::getUniqueFileName($replicatedMedia->file_name)
            )
        );
        $replicatedMedia->created_at = Carbon::now();
        $replicatedMedia->save();
        $replicatedMedia->saveUserId(auth()->user()->id);

        return $replicatedMedia;
    }

    public function replace(
        UploadedFile $file,
        Media $media,
        MediaStorage $mediaStorage
    ): Media {
        $this->fillMediaWithMediaAsset(
            $media,
            $mediaStorage->upload($file, $media->file_name)
        );
        $media->save();

        return $media;
    }

    public function rename(
        Media $media,
        string $fileName,
        MediaStorage $mediaStorage
    ): Media {
        $fileName = MediaService::getUniqueFileName(
            Str::lower($fileName),
            [],
            ($media->file_type != 'image' ? $media->extension : null)
        );

        $asset = $mediaStorage->rename(
            $media->file_name,
            $fileName,
            $media->file_type
        );

        $data = [];
        $data['file_name'] = $asset->fileName;
        $data['file_url'] = $asset->fileUrl;
        $data['version'] = $asset->version;
        $data['assets'] = $asset->assets;

        $media->update($data);

        return $media;
    }

    public function sanitizeFileName(string $fileName): string
    {
        // line by line explained: https://stackoverflow.com/a/19018736/8368172

        $sanitized = strip_tags($fileName);
        $sanitized = preg_replace('/[\r\n\t ]+/', ' ', $sanitized);
        $sanitized = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $sanitized);
        $sanitized = strtolower($sanitized);
        $sanitized = html_entity_decode( $sanitized, ENT_QUOTES, "utf-8" );
        $sanitized = htmlentities($sanitized, ENT_QUOTES, "utf-8");
        $sanitized = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $sanitized);
        $sanitized = str_replace(' ', '-', $sanitized);
        $sanitized = rawurlencode($sanitized);
        $sanitized = str_replace('%', '-', $sanitized);
        $sanitized = (substr($sanitized, -1) === '-') ? rtrim($sanitized, '-') : $sanitized;
        return $sanitized;
    }

    public function destroy(Media $media, MediaStorage $mediaStorage)
    {
        $mediaStorage->destroy($media->file_name, $media->file_type);
        $media->delete();
    }

    public static function getExtensions(): array
    {
        return collect(config('constants.extensions'))
            ->flatten()
            ->all();
    }

    public static function getDottedExtensions(): array
    {
        return array_map(
            function ($extension) {
                return '.'.$extension;
            },
            self::getExtensions()
        );
    }

    public function uploadUserMeta(
        UploadedFile $file,
        MediaStorage $mediaStorage,
        User $user,
    ): Media {
        $media = new Media();

        $extension = null;

        $clientExtension = $file->getClientOriginalExtension();

        $fileName = preg_replace(
            '/[^a-z0-9]+/',
            '-',
            Str::lower(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
        );

        if ($this->isOriginalExtensionNeeded($file)) {
            $extension = $clientExtension;
        }

        $folder = $this->getFolderPrefix().'user_assets/'.$user->id;

        $fileName = $this->getUniqueFileName(
            $fileName,
            [],
            $extension,
            $folder
        );

        $this->fillMediaWithMediaAsset(
            $media,
            $mediaStorage->upload($file, $fileName, $extension, $folder)
        );

        $media->type = Media::TYPE_USER_META;
        $media->save();
        $media->saveUserId(auth()->user()->id);

        return $media;
    }

    public function setMedially(Model $relatedModel, array $mediaIds = [])
    {
        $media = Media::whereIn('id', $mediaIds)
            ->whereNull('medially_id')
            ->get();

        foreach ($media as $medium) {
            $medium->medially()->associate($relatedModel);
            $medium->save();
        }
    }

    protected function getFolderPrefix(): ?string
    {
        return (!App::environment('production') ? config('app.env').'_' : null);
    }
}
