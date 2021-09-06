<?php

namespace App\Http\Controllers;

use App\Entities\CloudinaryStorage;
use App\Http\Requests\{
    MediaStoreRequest,
    MediaUpdateImageRequest,
    MediaUpdateRequest
};
use App\Models\Media;
use App\Services\{
    MediaService,
    TranslationService
};
use Illuminate\Http\Request;
use Inertia\Inertia;

class MediaController extends Controller
{
    private $mediaService;

    protected $model = Media::class;

    protected $baseRouteName = 'admin.media';
    protected $recordsPerPage = 12;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Inertia::render('Media/Index', [
            'baseRouteName' => $this->baseRouteName,
            'defaultLocale' => TranslationService::getDefaultLocale(),
            'pageNumber' => $request->page,
            'pageQueryParams' => array_filter($request->all('term', 'view')),
            'records' => $this
                ->mediaService
                ->getRecords($request->term, [], $this->recordsPerPage),
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
        $media = $this->mediaService->upload(
            $request->file,
            $request->input('file_name'),
            new CloudinaryStorage()
        );

        if ($request->has('translations')) {
            foreach ($request->input('translations') as $locale => $translation) {
                $data[$locale] = $translation;
            }
            $media->update($data);
        }

        return $media;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MediaStoreRequest $request)
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
    public function apiStore(MediaStoreRequest $request)
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
    public function update(MediaUpdateRequest $request, Media $media)
    {
        $data = [];
        $fileName = $request->input('file_name');

        if ($media->file_name != $fileName) {
            $media = $this->mediaService->rename(
                $media,
                $fileName,
                new CloudinaryStorage()
            );
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
        $this->mediaService->destroy($media, new CloudinaryStorage());

        $request->session()->flash('message', 'Media deleted successfully!'.$media->file_name);

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

    public function saveAsMedia(Request $request, Media $media)
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

        return redirect()->back();
    }

    public function listImages(Request $request)
    {
        $records = $this->mediaService->getRecords(
            $request->term,
            ['image'],
            $this->recordsPerPage
        );

        return $request->ajax() ? $records : abort(404);
    }
}
