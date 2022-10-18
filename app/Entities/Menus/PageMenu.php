<?php

namespace App\Entities\Menus;

use App\Contracts\MenuInterface;
use App\Models\MenuItem;
use App\Services\ModuleService;
use Modules\Space\Entities\MenuItem as SpaceMenuItem;
use Modules\Space\Entities\Page as SpacePage;

class PageMenu extends BaseMenu implements MenuInterface
{
    private $isSpaceModuleActive = false;

    public function __construct($menuItem, $locale)
    {
        parent::__construct($menuItem, $locale);

        $this->page_id = $menuItem->page_id;
        $this->isSpaceModuleActive = app(ModuleService::class)->isModuleActive('space');
    }

    protected function getEagerLoads(): array
    {
        return [
            'page' => function ($query) {
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
    public function getModel(): ?MenuItem
    {
        if ($this->menuItem == null && $this->id) {
            $this->loadModel();
        }

        if (!$this->menuItem->page) {
            $this->loadAdditionalModel();
        }

        return $this->menuItem;
    }

    public function getUrl(): string
    {
        try {
            $pageTranslation = $this->getModel()
                ->page
                ->translateOrDefault($this->getLocaleTranslation());

            if (!$this->isDefaultPage()) {
                return $this->getAdditionalUrl($pageTranslation);
            }
        } catch (\Throwable $th) {
            return $this->fallbackUrl();
        }

        return $this->getTranslatedUrl(
            route('frontend.pages.show', [
                'page_translation' => $pageTranslation->slug,
            ])
        );
    }

    private function loadAdditionalModel(): void
    {
        if ($this->isSpaceModuleActive) {
            $this->menuItem = SpaceMenuItem::
                when($this->getEagerLoads(), function ($query) {
                    $query->with($this->getEagerLoads());
                })
                ->find($this->id);
        }
    }

    private function getAdditionalUrl($pageTranslation): string
    {
        if (
            $this->isSpaceModuleActive
            && $pageTranslation->page->type == SpacePage::TYPE
        ) {
            return $this->getTranslatedUrl(
                    route('frontend.spaces.show', [
                    'page_translation' => $pageTranslation->slug,
                ])
            );
        }

        return $this->fallbackUrl();
    }

    private function isDefaultPage(): bool
    {
        return $this->getModel()->page->type == null;
    }

    private function fallbackUrl()
    {
        return '';
    }
}
