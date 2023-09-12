<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;

class SocialMediaShareWidget extends BaseWidget implements WidgetInterface
{
    protected $component = 'SocialMediaShare';

    protected function getData(): array
    {
        return [
            'profilePageUrl' => $this->user->profile_page_url,
            'socialMediaShare' => [
                'facebook' => [
                    'url' => $this->user->profile_page_url,
                    'title' => 'Hello, ' . ucwords($this->user->first_name) . ' here!',
                    'description' => '',
                    'quote' => '',
                    'hashtags' => '',
                    'icon' => 'fa-brands fa-facebook',
                    'class' => null,
                    'text' => 'Facebook',
                ],
                'twitter' => [
                    'url' => $this->user->profile_page_url,
                    'title' => 'Hello, ' . ucwords($this->user->first_name) . ' here!',
                    'description' => '',
                    'quote' => '',
                    'hashtags' => '',
                    'icon' => 'fa-brands fa-twitter',
                    'class' => null,
                    'text' => 'Twitter',
                ],
                'linkedIn' => [
                    'url' => $this->user->profile_page_url,
                    'title' => 'Hello, ' . ucwords($this->user->first_name) . ' here!',
                    'description' => '',
                    'quote' => '',
                    'hashtags' => '',
                    'icon' => 'fa-brands fa-linkedin-in',
                    'class' => null,
                    'text' => 'LinkedIn',
                ],
            ],
        ];
    }

    public function canBeAccessed(): bool
    {
        $canPublicPage = $this->user->hasPublicPage;

        return parent::canBeAccessed()
            && $canPublicPage;
    }
}
