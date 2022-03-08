<?php

namespace App\Entities\Forms\Fields;

use App\Models\User;

class Video extends Text
{
    protected $type = "Video";

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->placeholder = $data['placeholder'] ?? "Youtube or Vimeo Video URL";
    }

    public function validationRules(User $entity = null): array
    {
        $rules = parent::validationRules($entity);

        $rules[$this->name][] = "url";

        // @see https://www.regextester.com/96461
        // @see https://stackoverflow.com/questions/5612602/improving-regex-for-parsing-youtube-vimeo-urls
        $regex = '^(http:\/\/|https:\/\/)(vimeo\.com|youtu\.be|www\.youtube\.com|player\.vimeo\.com)\/((video\/|embed\/|watch\?v=|v\/)|[\w\/\S]+)([\?]\S*)?$';

        $rules[$this->name][] = 'regex:/'.$regex.'/i';

        return $rules;
    }
}
