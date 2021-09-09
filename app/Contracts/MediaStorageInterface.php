<?php

namespace App\Contracts;

use Symfony\Component\HttpFoundation\File\UploadedFile as File;

interface MediaStorageInterface {
    public function destroy(string $fileName);
    public function rename(string $fromName, string $toName);
    public function upload(File $file);
}
