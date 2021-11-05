<?php

namespace App\Contracts;

use App\Entities\MediaAsset;
use Illuminate\Http\UploadedFile;

interface MediaStorageInterface
{
    public function destroy(string $fileName);

    public function rename(string $fromName, string $toName);

    public function upload(
        UploadedFile $file,
        string $fileName = null,
        string $extension = null,
        string $folder = null
    ): MediaAsset;
}
