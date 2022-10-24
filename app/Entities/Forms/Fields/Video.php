<?php

namespace App\Entities\Forms\Fields;

class Video extends Text
{
    protected $type = "Video";

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->placeholder = $data['placeholder'] ?? "Youtube or Vimeo Video URL";
    }

    public function validationRules(): array
    {
        $rules = parent::validationRules();

        $rules[$this->name][] = "url";

        $regex = config('constants.regex.youtube_vimeo_url');

        $rules[$this->name][] = 'regex:/'.$regex.'/i';

        return $rules;
    }
}
