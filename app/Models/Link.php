<?php

namespace App\Models;

use App\Services\LinkService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

class Link extends Model
{
    use HasFactory;

    const TYPE_SOCIAL_MEDIA = 1;

    public $timestamps = false;

    protected $fillable = ['image_url', 'url', 'file_name'];

    public function syncLinks(
        array $links,
        int $type
    ) {
        $affectedIds = $this->updateLinks($links, $type);

        $unusedMenuItems = self::whereNotIn('id', $affectedIds)->get();

        foreach ($unusedMenuItems as $link) {
            $linkService = new LinkService();

            $linkService->deleteImageOnCloudStorage($link['file_name']);

            $link->delete();
        }
    }

    private function updateLinks(
        array $links,
        int $type
    ) {
        $affectedIds = collect([]);
        foreach ($links as $link) {
            if ($link['file'] !== null) {
                $linkService = new LinkService();

                $upload = $linkService->uploadImageToCloudStorage(
                    $link['file'],
                    Str::random(10),
                    (!App::environment('production') ? config('app.env') : null)
                );

                $link['file_name'] = $upload->fileName;
                $link['image_url'] = $upload->fileUrl;
            }

            $affectedLink = self::updateOrCreate(
                [
                    'id' => $link['id'],
                    'type' => $type,
                ],
                $link
            );

            $affectedIds->push($affectedLink->id);
        }

        return $affectedIds->flatten()->all();
    }
}
