<?php

namespace App\Http\Controllers;

use App\Entities\Caches\MenuCache;
use App\Entities\Caches\ModuleCache;
use App\Models\Module;
use App\Services\ModuleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Booking\Events\ModuleDeactivated;

class ModuleController extends CrudController
{
    protected $title = "Module";
    protected $baseRouteName = 'admin.settings.modules.';

    public function __construct(
        private ModuleService $moduleService,
        private ModuleCache $moduleCache,
    ) {}

    public function index(Request $request)
    {
        Inertia::share('i18n', [
            'actions' => __("Actions"),
            'activate' => __("Activate"),
            'deactivate' => __("Deactivate"),
            'name' => __("Name"),
            'status' => __("Status"),
            'save' => __("Save"),
        ]);

        return Inertia::render('Module/Index', [
            'records' => $this->moduleService->getRecords(),
            'pageQueryParams' => array_filter($request->only('term')),
            'title' => $this->getIndexTitle(),
        ]);
    }

    private function editData(Module $module, array $additionalData = []): array
    {
        return [
            ...[
                'baseRouteName' => $this->baseRouteName,
                'breadcrumbs' => [
                    [
                        'title' => $this->getIndexTitle(),
                        'url' => route($this->baseRouteName.'index'),
                    ],
                    [
                        'title' => $this->getEditTitle(),
                    ],
                ],
                'tabs' => $this->moduleService->tabs($module),
                'title' => __('Editing :title :resource', [
                    'title' => $module->name,
                    'resource' => $this->title
                ]),
            ],
            ...$additionalData,
        ];
    }

    public function edit(Request $request, Module $module)
    {
        Inertia::share('i18n', [
            'activate' => __("Activate"),
            'deactivate' => __("Deactivate"),
            'general' => __("General"),
            'navigations' => __("Navigations"),
            'title' => __("Title"),
            'update' => __("Update"),
            'cancel' => __("Cancel"),
            'activation_confirmation_title' => __("Activation"),
            'activation_confirmation_message' => __("Are you sure want to activate :module module?", ['module' => $module->title]),
        ]);

        return Inertia::render('Module/Edit', $this->editData($module, [
            'record' => $this->moduleService->getRecord($module),
            'tabIndex' => 0,
        ]));
    }

    public function update(Request $request, Module $module)
    {
        $this->moduleService->updateGeneral($module, $request->only('title'));

        app(MenuCache::class)->flush();
        $this->moduleCache->flush();

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __($this->title)
        ]);
    }

    public function activate(Request $request, Module $module)
    {
        $this->moduleService->activate($module);

        $this->moduleService->onActivated($module);

        $this->moduleCache->flush();

        $this->generateFlashMessage("The action ran successfully!");
    }

    public function deactivate(Request $request, Module $module)
    {
        $this->moduleService->deactivate($module);

        $this->moduleService->onDeactivated($module);

        $this->moduleCache->flush();

        $this->generateFlashMessage("The action ran successfully!");
    }

    public function editNavigations(Request $request, Module $module)
    {
        Inertia::share('i18n', [
            'title' => __("Title"),
            'general' => __("General"),
            'navigations' => __("Navigations"),
            'actions' => __("Actions"),
            'save' => __("Save"),
        ]);

        return Inertia::render('Module/EditNavigations', $this->editData($module, [
            'navigations' => $this->moduleService->getNavigations($module),
            'moduleId' => $module->id,
            'tabIndex' => 1,
        ]));
    }

    public function updateNavigations(Request $request, Module $module)
    {
        $this->moduleService->updateNavigations($module, $request->get('navigations', []));

        app(ModuleCache::class)->flush();
        app(MenuCache::class)->flush();

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Navigation')
        ]);
    }

    public function updateOrder(Request $request)
    {
        $this->moduleService->updateOrders(collect($request->get('modules')));

        app(ModuleCache::class)->flush();
        app(MenuCache::class)->flush();
    }


    public function confirmActivation(Module $module)
    {
        return [
            'title' => __("Are you sure want to deactivate :module module?", ['module' => $module->title]),
        ];
    }

    public function confirmDeactivation(Module $module)
    {
        return [
            'title' => __("Are you sure want to deactivate :module module?", ['module' => $module->title]),
            'messages' => $this->moduleService->deactivationMessages($module),
        ];
    }
}
