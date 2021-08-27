<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Services\MediaService;
use App\Services\TranslationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MediaController extends Controller
{
    protected $model = Media::class;

    protected $baseRouteName = 'admin.media';
    protected $recordsPerPage = 12;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Media/Index', [
            'records' => $this->getRecords(),
            'baseRouteName' => $this->baseRouteName,
            'defaultLocale' => TranslationService::getDefaultLocale(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Media/Create', [
            'baseRouteName' => $this->baseRouteName,
        ]);
    }

    protected function storeProcess(Request $request)
    {
        $fileName = $request->input('file_name');
        $extension = $request->file->extension();
        $mimeType = $request->file->getMimeType();

        $mediaDataOptions = [];
        $uploadFileOptions['resource_type'] = 'auto';

        if ( !(
            Str::startsWith($mimeType, 'image/')
            || Str::startsWith($mimeType, 'video/')
            || $extension == 'pdf'
        )) {
            $mediaDataOptions['extension'] = $extension;
        }

        $fileName = MediaService::getUniqueFileName(
            $fileName,
            [],
            $mediaDataOptions['extension'] ?? null
        );
        $uploadFileOptions['public_id'] = $fileName;

        $uploadedFile = cloudinary()->upload(
            $request->file('file')->getRealPath(),
            $uploadFileOptions,
        );

        $media = new Media();
        $this->setMediaData($media, $uploadedFile, $mediaDataOptions);
        $media->save();

        foreach ($request->input('translations') as $locale => $translation) {
            $data[$locale] = $translation;
        }
        $media->update($data);

        return $media;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->storeProcess($request);
        return redirect()->route($this->baseRouteName.'.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function apiStore(Request $request)
    {
        $media = $this->storeProcess($request);
        return $media->attributesToArray();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Media $media)
    {
        return Inertia::render('Media/Create', [
            'record' => $media,
            'baseRouteName' => $this->baseRouteName,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Media $media)
    {
        $data = [];
        $fileName = $request->input('file_name');

        if ($media->file_name != $fileName) {

            $fileName = MediaService::getUniqueFileName(
                $fileName,
                [],
                ($media->file_type != 'image' ? $media->extension : null)
            );

            $response = cloudinary()->uploadApi()->rename(
                $media->file_name,
                $fileName,
                ["resource_type" => $media->file_type]
            );

            $data['file_name'] = $response['public_id'];
            $data['file_url'] = $response['secure_url'];
            $data['version'] = $response['version'];
            $data['assets'] = $response;
        }

        foreach ($request->input('translations') as $locale => $translation) {
            $data[$locale] = $translation;
        }

        $assignedLocales = array_keys($media->getTranslationsArray());
        $providedLocales = array_keys($request->input('translations', []));
        $localeToBeDeleted = array_diff($assignedLocales, $providedLocales);

        $media->update($data);

        if (!empty($localeToBeDeleted)) {
            $media->deleteTranslations($localeToBeDeleted);
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Media $media)
    {
        cloudinary()->destroy($media->file_name);

        $media->delete();

        $request->session()->flash('message', 'Media deleted successfully!'.$media->file_name);

        return redirect()->back();
    }

    public function getRecords()
    {
        $records = $this->model::orderBy('id', 'DESC')
            ->with([
                'translations' => function ($q) {
                    $q->select(['id', 'media_id', 'alt', 'locale']);
                },
            ])
            ->paginate($this->recordsPerPage);

        $records->getCollection()->transform(function ($record) {
            $record->tag_url = cloudinary()
                   ->getImageTag($record->file_name)
                   ->resize(Resize::pad(96))
                   ->serialize();
            $record->_thumbnail_url = $record->thumbnailUrl;
            $record->readable_size = $record->readableSize;
            $record->is_image = $record->isImage;
            return $record;
        });

        return $records;
    }

    public function uploadImage(Request $request)
    {
        $fileName = pathinfo($request->input('filename'))['filename'];

        $fileName = MediaService::getUniqueFileName($fileName);

        $uploadedFile = cloudinary()->upload($request->file('image')->getRealPath(), [
            'public_id' => $fileName,
        ]);

        $media = new Media();
        $this->setMediaData($media, $uploadedFile);
        $media->save();

        return response()->json(['imagePath' => $media->file_url]);
    }

    public function updateImage(Request $request, Media $media)
    {
        $uploadedFile = cloudinary()->upload($request->file('image')->getRealPath(), ['public_id' => $media->file_name]);

        $this->setMediaData($media, $uploadedFile);
        $media->save();

        return $request->ajax()
            ? redirect()->back()
            : response()->json(['imagePath' => $media->file_url]);
    }

    public function saveAsMedia(Request $request, Media $media)
    {
        $uploadedFile = cloudinary()->upload(
            $request->file('image')->getRealPath(),
            ['public_id' => MediaService::getUniqueFileName($media->file_name)]
        );

        $replicatedMedia = $media->replicate();
        $this->setMediaData($replicatedMedia, $uploadedFile);
        $replicatedMedia->created_at = Carbon::now();
        $replicatedMedia->save();

        $replicatedMedia
            ->translations()
            ->saveMany($media->translations->map(function ($translation, $key) {
                return $translation->replicate();
            }));

        return $request->ajax()
            ? redirect()->back()
            : response()->json(['imagePath' => $media->file_url]);
    }

    public function listImages(Request $request)
    {
        $records = Media::image()
            ->orderBy('id', 'DESC')
            ->paginate($this->recordsPerPage);

        $records->getCollection()->transform(function ($record) {
            $record->_thumbnail_url = $record->thumbnailUrl;
            $record->is_image = $record->isImage;
            return $record;
        });

        return $request->ajax() ? $records : abort(404);
    }

    protected function setMediaData(Media &$media, $asset, $options = [])
    {
        $media->extension = $options['extension'] ?? $asset->getExtension();
        $media->file_name = $asset->getFileName();
        $media->file_type = $asset->getFileType();
        $media->file_url = $asset->getSecurePath();
        $media->size = $asset->getSize();
        $media->version = $asset->getVersion();
        $media->assets = $asset->getResponse();
    }
}
