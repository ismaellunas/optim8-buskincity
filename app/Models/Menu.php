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

                $className = self::getTypeMenuClass($menuItem['type']);
                $typeMenu = new $className(['id' => $menuItem['id']]);
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

    public function syncMenuItems(array $menuItems)
    {
        $affectedIds = $this->updateMenuItems($menuItems);

        $unusedMenuItems = $this->menuItems->whereNotIn('id', $affectedIds);

        foreach ($unusedMenuItems as $menuItem) {
            $menuItem->delete();
        }
    }

    private static function getTypeMenuClass(string $type)
    {
        return "\App\Entities\Menus\\".MenuItem::TYPE_VALUES[$type]."Menu";
    }

    private function updateMenuItems(
        array $menuItems,
        ?int $parentId = null
    ): array {
        $order = 1;

        $affectedIds = collect([]);

        foreach ($menuItems as $menuItem) {

            $menuItem['order'] = $order;
            $menuItem['parent_id'] = $parentId;
            $menuItem['menu_id'] = $this->id;

            $className = self::getTypeMenuClass($menuItem['type']);

            $typeMenu = new $className($menuItem);

            $affectedMenuItem = MenuItem::updateOrCreate(
                [
                    'id' => $typeMenu->id,
                    'menu_id' => $typeMenu->menu_id,
                ],
                $typeMenu->getAttributes()
            );

            if (count($menuItem['children']) > 0) {
                $childrenIds = $this->updateMenuItems(
                    $menuItem['children'],
                    $affectedMenuItem['id']
                );

                $affectedIds->push($childrenIds);
            }

            $order++;

            $affectedIds[] = $affectedMenuItem->id;
        }

        return $affectedIds->flatten()->all();
    }

    private function setNullInput($input)
    {
        $className = "\App\Entities\Menus\\".MenuItem::TYPE_VALUES[$input['type']]."Menu";
        $menu = new $className();

        foreach ($menu->nullFields() as $nullField) {
            $input[$nullField] = null;
        }

        return $input;
    }
}
