<?php

namespace App\View\Components\Builder\Content;

class Video extends BaseContent
{
    public $url;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->url = $this->getUrl();
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
