<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'locale',
    ];

    const TYPE_HEADER = 1;
    const TYPE_FOOTER = 2;

    public function saveFromInputs(array $inputs)
    {
        $this->fill($inputs);
        $this->save();
    }

    public static function generateMenuItems(
        string $locale = 'en',
        int $type = self::TYPE_HEADER
    ): array {
        $menu = self::getMenu($locale, $type);

        return $menu ? self::createArrayMenuItems($menu) : [];
    }

    private static function getMenu(
        string $locale,
        int $type
    ) {
        return self::with([
                'menuItems' => function ($query) {
                    $query->orderBy('order', 'ASC')
                        ->orderBy('parent_id', 'ASC');
                }
            ])
            ->where('locale', $locale)
            ->where(function ($query) use ($type) {
                if ($type == self::TYPE_HEADER) {
                    $query->header();
                } else {
                    $query->footer();
                }
            })
            ->first();
    }

    private static function createArrayMenuItems(
        object $menu,
        ?int $parentId = null
    ): array {
        $menus = [];
        foreach ($menu->menuItems as $menuItem) {
            if ($menuItem['parent_id'] == $parentId) {
                $children = self::createArrayMenuItems($menu, $menuItem['id']);

                if ($children) {
                    $menuItem['children'] = $children;
                } else {
                    $menuItem['children'] = [];
                }

                $className = "\App\Entities\Menus\\".MenuItem::TYPE_VALUES[$menuItem['type']]."Menu";
                $typeMenu = new $className($menuItem['id']);
                $menuItem['link'] = $typeMenu->getUrl();

                $menus[] = $menuItem;
            }
        }

        return $menus;
    }

    // Scope
    public function scopeHeader($query)
    {
        return $query->where('type', self::TYPE_HEADER);
    }

    public function scopeFooter($query)
    {
        return $query->where('type', self::TYPE_FOOTER);
    }

    // Relation
    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'menu_id');
    }
}
