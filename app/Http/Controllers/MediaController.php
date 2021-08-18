<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Services\TranslationService;
use Cloudinary\Transformation\Resize;
use Illuminate\Http\Request;
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
            'localeOptions' => TranslationService::getLocaleOptions(),
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $uploadedFile = cloudinary()->upload($request->file('file')->getRealPath());

        $media = new Media();
        $media->file_name = $uploadedFile->getFileName();
        $media->file_url = $uploadedFile->getSecurePath();
        $media->size = $uploadedFile->getSize();
        $media->file_type = $uploadedFile->getFileType();
        $media->save();

        return redirect()->route($this->baseRouteName.'.index');
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
    public function update(Request $request, $id)
    {
        //
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

        return redirect()->route($this->baseRouteName.'.index');
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
            return $record;
        });

        return $records;
    }

    public function uploadImage(Request $request)
    {
        $uploadedFile = cloudinary()->upload($request->file('image')->getRealPath());

        $media = new Media();
        $media->file_name = $uploadedFile->getFileName();
        $media->file_url = $uploadedFile->getSecurePath();
        $media->size = $uploadedFile->getSize();
        $media->file_type = $uploadedFile->getFileType();
        $media->save();

        return response()->json(['imagePath' => $media->file_url]);
    }

    public function listImages(Request $request)
    {
        $records = Media::orderBy('id', 'DESC')->paginate($this->recordsPerPage);

        $records->getCollection()->transform(function ($record) {
            $record->_thumbnail_url = $record->thumbnailUrl;
            return $record;
        });

        return $request->ajax() ? $records : abort(404);
    }
}
