<?php

namespace App\Services;

use App\Contracts\MediaStorageInterface as MediaStorage;
use App\Entities\CloudinaryAsset;
use App\Entities\MediaAsset;
use App\Helpers\HumanReadable;
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

class MediaService
{
    public static $userAssetsFolder = 'user_assets/';
    public static $profilePictureFolder = 'profiles/';

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
            $searchFileName = $folder.$searchFileName;
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
        $hasAccessToOtherMedia = $user->can('manageOtherMedia', Media::class);

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
                'mediable' => function ($q) {
                    $q->select([
                        'id',
                        'media_id',
                        'mediable_id',
                        'mediable_type',
                    ]);
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
        $record->can_edit_existing_media = auth()->user()->can('update', $record);
        $record->optimize_file_url = $record->optimizedImageUrl != ''
            ? $record->optimizedImageUrl
            : $record->file_url;

        $record->append('is_in_use');

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

    protected function folderPath(string $suffix = null): ?string
    {
        $path = config('filesystems.folder_prefix');

        if ($suffix) {
            $path = $path.$suffix.'/';
        }

        return $path;
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

        $folder = $this->folderPath($folder);

        $fileName = MediaService::getUniqueFileName(
            Str::lower($fileName),
            [],
            $extension,
            $folder
        );

        $params = [$file, $fileName, $extension, $folder];

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
    ): Media {
        $folder = self::$profilePictureFolder;

        $media = new Media();

        $extension = null;

        $clientExtension = $file->getClientOriginalExtension();

        if ($this->isOriginalExtensionNeeded($file)) {
            $extension = $clientExtension;
        }

        $folder = $this->folderPath($folder);

        $fileName = MediaService::getUniqueFileName(
            Str::lower($fileName),
            [],
            $extension,
            $folder
        );

        $params = [$file, $fileName, $extension, $folder];

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
        MediaStorage $mediaStorage,
    ): Media {
        $folder = $this->folderPath();

        $fileName = MediaService::getUniqueFileName(
            Str::lower($fileName),
            [],
            ($media->file_type != 'image' ? $media->extension : null),
            $folder,
        );

        $fileName = $folder.$fileName;

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

        $folder = $this->folderPath(self::$userAssetsFolder.$user->id);

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

    public function uploadUserMetaFromMedia(
        Media $sourceMedia,
        User $user,
    ): Media {
        $media = new Media();

        $extension = null;

        $clientExtension = $sourceMedia->extension;

        $fileName = $sourceMedia->slicedFileName;

        if (! in_array($sourceMedia->type, ['image', 'video'])) {
            $extension = $clientExtension;
        }

        $folder = $this->folderPath(self::$userAssetsFolder.$user->id);

        $fileName = $this->getUniqueFileName($fileName, [], null, $folder);

        $result = cloudinary()->upload(
            $sourceMedia->file_url,
            [
                'public_id' => $folder.'/'.$fileName,
                'resource_type' => $sourceMedia->assets->get('resource_type'),
                'invalidate' => false,
            ]
        );

        $response = CloudinaryAsset::createAssetFromApiResponse(
            $result->getResponse(),
            $extension
        );

        $this->fillMediaWithMediaAsset(
            $media,
            $response,
        );

        $media->type = Media::TYPE_USER_META;
        $media->save();
        $media->saveUserId(auth()->user()->id ?? $user->id);

        return $media;
    }

    public function uploadProfileFromMedia(
        Media $sourceMedia,
        User $user,
    ): Media {
        $fileName = $user->generateProfilePhotoFileName();

        $folder = $this->folderPath(self::$profilePictureFolder);

        $fileName = $this->getUniqueFileName($fileName, [], null, $folder);

        $result = cloudinary()->upload(
            $sourceMedia->file_url,
            [
                'public_id' => $folder.'/'.$fileName,
                'resource_type' => $sourceMedia->assets->get('resource_type'),
                'invalidate' => false,
            ]
        );

        $response = CloudinaryAsset::createAssetFromApiResponse(
            $result->getResponse()
        );

        $media = new Media();

        $this->fillMediaWithMediaAsset(
            $media,
            $response,
        );

        $media->type = Media::TYPE_PROFILE;
        $media->save();
        $media->saveUserId(auth()->user()->id ?? $user->id);

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

    public static function defaultMediaLibraryInstructions(): array
    {
        return [
            __('Accepted file extensions: :extensions.', [
                'extensions' => implode(', ', config('constants.extensions.image'))
            ]),
            __('Max file size: :filesize.', [
                'filesize' => HumanReadable::bytesToHuman(
                    SettingService::maxFileSize() * 1024
                )
            ]),
        ];
    }

    public static function videoMediaLibraryInstructions(): array
    {
        return [
            __('Accepted file extensions: :extensions.', [
                'extensions' => implode(', ', config('constants.extensions.video'))
            ]),
            __('Max file size: :filesize.', [
                'filesize' => HumanReadable::bytesToHuman(
                    SettingService::maxFileSize() * 1024
                )
            ]),
        ];
    }

    public static function logoMediaLibraryInstructions(): array
    {
        return [
            __('Accepted file extensions: :extensions.', [
                'extensions' => implode(', ', config('constants.extensions.image'))
            ]),
            __('Max file size: :filesize.', [
                'filesize' => HumanReadable::bytesToHuman(
                    SettingService::maxFileSize() * 1024
                )
            ]),
            __('Recommended dimension: :dimension.', [
                'dimension' => config('constants.recomended_dimensions.logo')
            ]),
        ];
    }

    public static function profilePictureInstructions(): array
    {
        return [
            __('Accepted file extensions: :extensions.', [
                'extensions' => implode(', ', config('constants.extensions.image'))
            ]),
            __('Max file size: :filesize.', [
                'filesize' => HumanReadable::bytesToHuman(
                    config('constants.file_size.profile_picture') * 1024
                )
            ]),
            __('Recommended dimension: :dimension.', [
                'dimension' => config('constants.recomended_dimensions.profile_picture'),
            ]),
        ];
    }

    public static function qrCodeMediaLibraryInstructions(): array
    {
        return [
            __('Accepted file extensions: :extensions.', [
                'extensions' => implode(', ', config('constants.extensions.image'))
            ]),
            __('Max file size: :filesize.', [
                'filesize' => HumanReadable::bytesToHuman(
                    SettingService::maxFileSize() * 1024
                )
            ]),
            __('Recommended dimension: :dimension.', [
                'dimension' => config('constants.recomended_dimensions.qr_code')
            ]),
        ];
    }

    public static function defaultMediaLibraryTranslations(): array
    {
        return [
            'search' => __('Search'),
            'media_detail' => __('Media detail'),
            'filter' => __('Filter'),
            'file_name' => __('File name'),
            'alternative_text' => __('Alternative text'),
            'description' => __('Description'),
            'date_modified' => __('Date modified'),
            'type' => __('Type'),
            'size' => __('Size'),
            'actions' => __('Actions'),
            'save' => __('Save'),
            'save_as_new' => __('Save as new'),
            'cancel' => __('Cancel'),
            'done' => __('Done'),
            'delete' => __('Delete'),
            'edit_image' => __('Edit :resource', ['resource' => __('Image')]),
            'are_you_sure' => __('Are you sure?'),
            'yes' => __('Yes'),
            'edit_resource' => __('Edit :resource', ['resource' => __('Media')]),
            'warning_edit_resource' => __('This resource is still being used in another place. If you :action this resource, it may have an effect on that other place.', ['action' => 'edit']),
            'update_media_success' => __('The :resource was updated!', ['resource' => __('Media')]),
        ];
    }
}
