<?php

namespace App\Interfaces;

use Symfony\Component\HttpFoundation\File\UploadedFile as File;

interface MediaStorage {
    public function destroy(string $fileName);
    public function rename(string $fromName, string $toName);
    public function upload(File $file);
}
