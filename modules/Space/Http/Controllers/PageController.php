<?php

namespace Modules\Space\Http\Controllers;

use App\Models\PageTranslation;
use App\Traits\FlashNotifiable;
use Illuminate\Routing\Controller;
use Modules\Space\Entities\Page;
use Modules\Space\Entities\Space;
use Modules\Space\Http\Requests\PageStoreRequest;
use Modules\Space\Http\Requests\PageUpdateRequest;

class PageController extends Controller
{
    use FlashNotifiable;

    public function store(PageStoreRequest $request, Space $space)
    {
        $page = new Page();
        $page->saveFromInputs($request->all());
        $page->saveAuthorId(auth()->id());

        $space->page_id = $page->id;
        $space->save();

        $this->generateFlashMessage('Page created successfully!');

        return back();
    }

    public function update(PageUpdateRequest $request, Space $space, Page $page)
    {
        $inputs = $request->all();

        $locale = (string) collect($inputs)->pluck('locale')->filter()->first();

        $oldStatus = $page->translate($locale)->status ?? null;

        $pageTranslation = $inputs[$locale] ?? [];

        $page->saveFromInputs($inputs);

        if (
            !empty($pageTranslation['page_id'])
            && $oldStatus == PageTranslation::STATUS_PUBLISHED
            && $pageTranslation['status'] == PageTranslation::STATUS_DRAFT
        ) {
            app(MenuService::class)->removePageFromMenus(
                $pageTranslation['page_id'],
                $pageTranslation['locale']
            );
        }

        $this->generateFlashMessage('Page updated successfully!');

        return back();
    }
}
