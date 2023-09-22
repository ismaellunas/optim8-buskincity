<?php

namespace App\View\Components\Builder\Content;

use Cohensive\OEmbed\Facades\OEmbed;

class Video extends BaseContent
{
    public $embed = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        parent::__construct($entity);

        $url = $this->getUrl();

        if ($url) {
            $this->embed = OEmbed::get($url);
        }
    }

    private function getUrl(): ?string
    {
        $url = $this->getConfig()['video']['url'] ?? null;
        $pattern = '/'.config('constants.regex.youtube_vimeo_url').'/i';

        if (!preg_match($pattern, $url)) {
            $url = null;
        }

        return $url;
    }
}
