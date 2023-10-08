<?php

namespace App\Entities\PublicPageProfile;

use App\Contracts\PublicPageProfileUrlInterface;
use App\Models\User;
use Illuminate\Support\Str;

class DefaultUrl implements PublicPageProfileUrlInterface
{
    public function __construct(
        protected User $user,
        protected array $parameters = [],
    ) {}

    public function url(): string
    {
        return route('frontend.profile', [
            ...[
                'user' => $this->user->unique_key,
                'slug' => $this->getSlug(),
            ],
            ...$this->parameters
        ]);
    }

    protected function getSlug(): string
    {
        return Str::slug($this->user->fullName);
    }
}