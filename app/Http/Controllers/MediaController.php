<?php

namespace App\Http\Controllers;

use App\Entities\CloudinaryStorage;
use App\Helpers\HumanReadable;
use App\Http\Requests\{
    MediaIndexRequest,
    MediaSaveAsImageRequest,
    MediaStoreRequest,
    MediaUpdateImageRequest,
};
use App\Models\Media;
use App\Services\MediaService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Inertia\Inertia;


class MediaController extends CrudController
{
    private $mediaService;

    protected $model = Media::class;

    protected $baseRouteName = 'admin.media';
    protected $recordsPerPage = 12;
    protected $title = 'Media';

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
        $this->authorizeResource(Media::class, 'medium');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(MediaIndexRequest $request)
    {
        $user = auth()->user();

        return Inertia::render('Media/Index', $this->getData([
            'can' => [
                'add' => $user->can('media.add'),
                'delete' => $user->can('media.delete'),
                'edit' => $user->can('media.edit'),
                'read' => $user->can('media.read'),
            ],
            'defaultLocale' => defaultLocale(),
            'pageNumber' => $request->page,
            'pageQueryParams' => array_filter($request->all('term', 'view', 'types')),
            'acceptedTypes' => $this->mediaService->getDottedExtensions(),
            'records' => $this
                ->mediaService
                ->getRecords($request->term, $request->types, $this->recordsPerPage),
            'title' => $this->getIndexTitle(),
            'instructions' => [
                'mediaLibrary' => [
                    __('Accepted file extensions: :extensions.', [
                        'extensions' => implode(',', MediaService::getExtensions()),
                    ]),
                    __('Max file upload: :maxupload.', [
                        'maxupload' => 5
                    ]),
                    __('Max file size: :filesize.', [
                        'filesize' => HumanReadable::bytesToHuman(
                            SettingService::maxFileSize() * 1024
                        )
                    ]),
                ],
            ],
            'i18n' => $this->translationIndexPage(),
        ]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Media/Create', $this->getData([
            'title' => $this->getCreateTitle(),
        ]));
    }

    private function storeProcess(array $inputs): array
    {
        $allMedia = [];

        foreach ($inputs as $input) {
            $media = $this->mediaService->upload(
                $input['file'],
                $this->mediaService->sanitizeFileName($input['file_name']),
                new CloudinaryStorage()
            );

            if (!empty($input['translations'])) {
                foreach ($input['translations'] as $locale => $translation) {
                    $data[$locale] = $translation;
                }

                $media->update($data);
            }

            $media->append(['is_image', 'display_file_name']);

            $allMedia[] = $media;
        }

        return $allMedia;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MediaStoreRequest $request)
    {
        $this->syncMedia($request);

        return redirect()->route($this->baseRouteName.'.index');
    }

    private function syncMedia(Request $request)
    {
        $storeInputs = collect($request->all())
            ->filter(fn ($media) => empty($media['id']))
            ->all();

        if (!empty($storeInputs)) {
            $this->storeProcess($storeInputs);

            $this->generateFlashMessage('The :resource was created!', [
                'resource' => __('Media')
            ]);
        }

        $updateInputs = collect($request->all())
            ->filter(fn ($media) => !empty($media['id']))
            ->all();

        if (!empty($updateInputs)) {
            $this->updateProsess($updateInputs);

            $this->generateFlashMessage('The :resource was updated!', [
                'resource' => __('Media')
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function apiStore(MediaStoreRequest $request)
    {
        $allMedia = $this->storeProcess($request->all());

        return $allMedia;
    }

    private function updateProsess(array $inputs)
    {
        $allMedia = [];

        foreach ($inputs as $input) {
            $data = [];

            $media = Media::find($input['id']);
            $fileName = $this->mediaService->sanitizeFileName($input['file_name']);

            if ($media->file_name != $fileName) {
                $media = $this->mediaService->rename(
                    $media,
                    $fileName,
                    new CloudinaryStorage()
                );
            }

            if (!empty($input['translations'])) {
                foreach ($input['translations'] as $locale => $translation) {
                    $data[$locale] = $translation;
                }
            }

            $assignedLocales = array_keys($media->getTranslationsArray());
            $providedLocales = array_keys($input['translations'] ?? []);
            $localeToBeDeleted = array_diff($assignedLocales, $providedLocales);

            $media->update($data);

            if (!empty($localeToBeDeleted)) {
                $media->deleteTranslations($localeToBeDeleted);
            }

            $allMedia[] = $media;
        }

        return $allMedia;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Media $media)
    {
        $this->mediaService->destroy($media, new CloudinaryStorage());

        $this->generateFlashMessage('The :resource was deleted!', [
            'resource' => __('Media')
        ]);

        return redirect()->back();
    }

    public function updateImage(MediaUpdateImageRequest $request, Media $media)
    {
        $media = $this->mediaService->replace(
            $request->file('image'),
            $media,
            new CloudinaryStorage()
        );

        return $request->ajax()
            ? redirect()->back()
            : response()->json(['imagePath' => $media->file_url]);
    }

    public function saveAsImage(MediaSaveAsImageRequest $request, Media $media)
    {
        $replicatedMedia = $this->mediaService->duplicateImage(
            $request->file('image'),
            $media,
            new CloudinaryStorage()
        );

        $replicatedMedia
            ->translations()
            ->saveMany($media->translations->map(function ($translation, $key) {
                return $translation->replicate();
            }));

        $this->generateFlashMessage('The :resource was created!', [
            'resource' => __('Media')
        ]);

        return redirect()->back();
    }

    public function lists(Request $request)
    {
        if ($request->user()->cannot('viewAny', Media::class)) {
            abort(403);
        }

        if ($request->type) {
            $this->isValidMediaType($request->type) ?? abort(403);
        }

        $records = $this->mediaService->getRecords(
            $request->term,
            $request->type ?? ['image'],
            $this->recordsPerPage
        );

        return $request->ajax() ? $records : abort(404);
    }

    private function isValidMediaType($type): bool
    {
        return collect(config('constants.extensions'))
            ->keys()
            ->contains(
                collect($type)->implode(',')
            );
    }

    private function translationIndexPage(): array
    {
        return [
            'search' => __('Search'),
            'filter' => __('Filter'),
            'file_name' => __('File name'),
            'alternative_text' => __('Alternative text'),
            'description' => __('Description'),
            'date_modified' => __('Date modified'),
            'type' => __('Type'),
            'size' => __('Size'),
            'actions' => __('Actions'),
            'media_detail' => __('Media detail'),
            'save' => __('Save'),
            'cancel' => __('Cancel'),
            'delete' => __('Delete'),
            'save_as_new' => __('Save as new'),
            'done' => __('Done'),
            'edit_image' => __('Edit :resource', ['resource' => __('Image')]),
            'are_you_sure' => __('Are you sure?'),
            'delete_resource' => __('Delete :resource', ['resource' => __('Media')]),
            'warning_delete_resource' => __('This resource is still being used in another place. If you delete this resource, it may have an effect on that other place.'),
        ];
    }
}
