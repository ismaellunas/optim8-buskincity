<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Models\PerformerApplication;

class PerformerApplicationLinkWidget implements WidgetInterface
{
    protected $baseRouteName = "performer-application-form";
    protected $componentName = "PerformerApplicationLink";
    protected $data = [];
    protected $title = "Performer Application";
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
        $this->data = $this->getWidgetData();
    }

    public function data(): array
    {
        $data = [
            'title' => $this->title,
            'componentName' => $this->componentName,
        ];

        if (!$this->hasSubmittedApplication()) {
            $data['data'] = $this->data;
        }

        return $data;
    }

    private function getWidgetData(): array
    {
        return [
            'baseRouteName' => $this->baseRouteName,
        ];
    }

    private function hasSubmittedApplication(): bool
    {
        return PerformerApplication::where('applicant_id', $this->user->id)
            ->exists();
    }

    public function canBeAccessed(): bool
    {
        return $this->user->roles->isEmpty();
    }
}