<?php

namespace Modules\Space\Menus;

use App\Contracts\MenuInterface;
use App\Entities\Menus\BaseMenu;
use App\Models\MenuItem;

class MenuUrl extends BaseMenu implements MenuInterface
{
    protected $modelName = MenuItem::class;

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
            $pageTranslation = $this->getModel()->menuItemable->page->translateOrDefault($this->locale);
        } catch (\Throwable $th) {
            return $this->fallbackUrl();
        }

        return route('frontend.spaces.show', [
            'slugs' => $pageTranslation->getSlugs(),
        ]);
    }
}