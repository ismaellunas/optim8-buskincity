<?php

namespace App\Entities\Menus\GenerateUrls;

use App\Contracts\MenuInterface;
use App\Models\MenuItem;
use App\Services\ModuleService;

class PageMenu extends BaseMenu implements MenuInterface
{
    public function __construct($menuItem, $locale)
    {
        parent::__construct($menuItem, $locale);

        $this->page_id = $menuItem->page_id;
    }

    protected function getEagerLoads(): array
    {
        return [
            'menuItemable' => function ($query) {
                $query->select([
                    'id',
                    'type'
                ]);
                $query->with('translations', function ($query) {
                    $query->select([
                        'id',
                        'page_id',
                        'locale',
                        'slug',
                    ]);
                });
            },
            'menu',
        ];
    }

    /**
     * @Override
     */
    public function getModel(): MenuItem
    {
        if ($this->menuItem == null && $this->id) {
            $this->loadModel();
        }

        return $this->menuItem;
    }

    public function getUrl(): string
    {
        try {
            $pageTranslation = $this->getModel()->menuItemable->translateOrDefault($this->locale);
        } catch (\Throwable $th) {
            return $this->fallbackUrl();
        }

        return $this->getTranslatedUrl(
            route('frontend.pages.show', [
                'page_translation' => $pageTranslation->slug,
            ])
        );
    }

    private function fallbackUrl()
    {
        return '';
    }
}
