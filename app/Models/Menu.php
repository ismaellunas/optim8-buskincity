<?php

namespace App\Models;

use App\Entities\Caches\MenuCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    const TYPE_HEADER = 1;
    const TYPE_FOOTER = 2;
    const TYPE_SOCIAL_MEDIA = 3;

    protected $fillable = [
        'type',
        'locale',
    ];

    public function saveFromInputs(array $inputs)
    {
        $this->fill($inputs);
        $this->save();
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

    public function scopeSocialMedia($query)
    {
        return $query->where('type', self::TYPE_SOCIAL_MEDIA);
    }

    public function scopeLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    public function scopeMenuItemsBuilder($query)
    {
        $query->with([
            'menuItems' => function ($query) {
                $query
                    ->orderBy('order', 'ASC')
                    ->orderBy('parent_id', 'ASC')
                    ->with([
                        'post' => function ($query) {
                            $query->select([
                                'id',
                                'slug',
                            ]);
                        },
                        'page' => function ($query) {
                            $query->select('id');
                            $query->with('translations', function ($query) {
                                $query->select([
                                    'id',
                                    'page_id',
                                    'locale',
                                    'slug',
                                ]);
                            });
                        },
                    ]);
            },
        ]);
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

        app(MenuCache::class)->flush();
    }

    private function updateMenuItems(
        array $menuItems,
        ?int $parentId = null
    ): array {
        $order = 1;

        $affectedIds = collect([]);

        $fillableAttributes = (new MenuItem())->getFillable();

        foreach ($menuItems as $menuItem) {

            $menuItem['order'] = $order;
            $menuItem['parent_id'] = $parentId;
            $menuItem['menu_id'] = $this->id;

            $menuItemAttributes = collect($menuItem)
                ->only($fillableAttributes)
                ->all();

            $affectedMenuItem = MenuItem::updateOrCreate(
                [
                    'id' => $menuItem['id'],
                    'menu_id' => $menuItem['menu_id'],
                ],
                $menuItemAttributes
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

    public function syncSocialMedia(array $inputs)
    {
        $affectedIds = $this->updateSocialMedia($inputs);

        $unusedMenuItems = $this->menuItems->whereNotIn('id', $affectedIds);

        foreach ($unusedMenuItems as $socialMedia) {
            $socialMedia->delete();
        }
    }

    private function updateSocialMedia(array $inputs): array
    {
        $affectedIds = collect([]);
        foreach ($inputs as $input) {
            $input['title'] = 'social-media';
            $affectedSocialMedia = MenuItem::updateOrCreate(
                [
                    'id' => $input['id'],
                    'menu_id' => $this->id,
                ],
                $input
            );

            $affectedIds->push($affectedSocialMedia->id);
        }

        return $affectedIds->flatten()->all();
    }
}
