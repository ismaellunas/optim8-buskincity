<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Models\Setting;
use App\Models\Page;
use Modules\FormBuilder\Entities\FormEntry;

class PerformerApplicationLinkWidget implements WidgetInterface
{
    protected $componentName = "PerformerApplicationLink";
    protected $data = [];
    protected $title = "Become a Performer";
    protected $user;

    private $settings = [];

    public function __construct()
    {
        $this->user = auth()->user();
        $this->settings = $this->getSettings();
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
        $pageUrl = null;
        $pageId = $this->settings['page_id'] ?? null;

        if ($pageId) {
            $page = Page::with([
                    'translations' => function ($query) {
                        $query->select([
                            'id',
                            'locale',
                            'slug',
                            'page_id'
                        ]);
                    }
                ])
                ->where('id', $pageId)
                ->first();

            if ($page) {
                $pageTranslation = $page->translateOrDefault(currentLocale());

                $pageUrl = $pageTranslation->slug ?? null;
            }
        }

        return [
            'pageUrl' => $pageUrl,
        ];
    }

    private function hasSubmittedApplication(): bool
    {
        $formId = $this->settings['form_id'] ?? null;

        if ($formId) {
            return FormEntry::with(['metas'])
                ->where('form_id', $formId)
                ->where('user_id', $this->user->id)
                ->exists();
        }

        return false;
    }

    public function canBeAccessed(): bool
    {
        return $this->user->roles->isEmpty();
    }

    private function getSettings()
    {
        return Setting::group('widget.performer_application')
            ->pluck('value', 'key')
            ->toArray();
    }
}