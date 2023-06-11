<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;

class SocialMediaShareWidget implements WidgetInterface
{
    protected $data = [];
    protected $title = "Share your page";
    protected $componentName = 'SocialMediaShare';
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
        $this->data = $this->getWidgetData();
    }

    public function data(): array
    {
        return [
            'title' => $this->title,
            'componentName' => $this->componentName,
            'data' => $this->data,
        ];
    }

    private function getWidgetData(): array
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
            'description' => __(
                'As a :role, you have a public page to share with your audience. It\'s just like your unique site within :appName. You can copy the link or share on your social media:',
                [
                    'role' => $this->user->roleName,
                    'appName' => config('app.name'),
                ]
            ),
        ];
    }

    public function canBeAccessed(): bool
    {
        $canPublicPage = $this->user->hasPublicPage;

        return $canPublicPage;
    }
}
