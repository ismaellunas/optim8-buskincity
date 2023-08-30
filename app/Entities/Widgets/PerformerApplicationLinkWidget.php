<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Models\Page;
use Modules\FormBuilder\Entities\FormEntry;

class PerformerApplicationLinkWidget extends BaseWidget implements WidgetInterface
{
    protected $component = "PerformerApplicationLink";

    protected function getData(): array
    {
        if ($this->hasSubmittedApplication()) {
            return [
                ...parent::getData(),
                ...[
                    'hasSubmitted' => $this->hasSubmittedApplication(),
                    'pageUrl' => "",
                ]
            ];
        }

        $pageUrl = "";
        $pageId = $this->storedSetting['setting']['page_id'] ?? null;

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
            ...parent::getData(),
            ...[
                'hasSubmitted' => $this->hasSubmittedApplication(),
                'pageUrl' => $pageUrl,
            ],
        ];
    }

    private function hasSubmittedApplication(): bool
    {
        $formId = $this->storedSetting['setting']['form_id'] ?? null;

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
        return parent::canBeAccessed()
            && $this->user->roles->isEmpty();
    }
}