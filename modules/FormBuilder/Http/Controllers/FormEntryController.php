<?php

namespace Modules\FormBuilder\Http\Controllers;

use App\Http\Controllers\CrudController;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormEntry;
use Modules\FormBuilder\Http\Requests\FormEntryArchiveRequest;
use Modules\FormBuilder\Http\Requests\FormEntryMarkAsReadRequest;
use Modules\FormBuilder\Services\FormBuilderService;
use Modules\FormBuilder\Services\FormEntryService;

class FormEntryController extends CrudController
{
    protected $baseRouteName = 'admin.form-builders.entries';
    protected $recordsPerPage = 10;
    protected $title = 'Entry';

    private $formEntryService;

    public function __construct(FormEntryService $formEntryService)
    {
        $this->formEntryService = $formEntryService;
    }

    private function getPaginator(Request $request, FormBuilderService $formBuilderService)
    {
        $scopes = [];

        if ($request->has('read')) {
            $scopes['read'] = $request->read;
        }

        if ($request->tab == 'archived') {
            $scopes['onlyTrashed'] = true;
            unset($scopes['read']);
        }

        return $formBuilderService->getEntryRecords(
            $request->route('form_builder'),
            $request->term,
            $scopes,
            $this->recordsPerPage,
        );
    }

    private function pageParams(Request $request, LengthAwarePaginator $paginator = null): array
    {
        $params = $request->query();

        $params['form_builder'] = is_string($request->form_builder) ? $request->form_builder : $request->form_builder->id;

        if ($paginator) {
            $params['page'] = $this->redirectToPage($request->page, $paginator->lastPage());
        }

        return $params;
    }

    private function redirectToPage(?int $currentPage, int $lastPage): ?int
    {
        return ($currentPage <= $lastPage) ? $currentPage : $lastPage;
    }

    public function index(FormBuilderService $formBuilderService, Request $request, Form $formBuilder)
    {
        $title = 'Entries - ' . $formBuilder->name;

        $paginator = $this->getPaginator($request, $formBuilderService);

        $user = auth()->user();

        return Inertia::render('FormBuilder::Entries', $this->getData([
            'breadcrumbs' => [
                [
                    'title' => __('Forms'),
                    'url' => route('admin.form-builders.index'),
                ],
                [
                    'title' => $title,
                ],
            ],
            'title' => $title,
            'formBuilder' => $formBuilder,
            'records' => $paginator,
            'fieldLabels' => collect(
                    $formBuilder->getFieldLabels()
                )
                ->slice(0, 3)
                ->all(),
            'fieldNames' => collect(
                    $formBuilder->getFieldNames()
                )
                ->slice(0, 3)
                ->all(),
            'pageQueryParams' => $request->query(),
            'readOptions' => $this->formEntryService->readOptions(),
            'can' => [
                'markAsRead' => $user->can('update', $formBuilder),
                'markAsUnread' => $user->can('update', $formBuilder),
                'archive' => $user->can('delete', $formBuilder),
                'restore' => $user->can('delete', $formBuilder),
            ],
        ]));
    }

    public function show(FormBuilderService $formBuilderService, Form $formBuilder, FormEntry $entry)
    {
        $user = auth()->user();
        $userEntry = $entry->user;

        $canRedirectUser = false;

        if ($userEntry) {
            $canRedirectUser = (
                !$userEntry->isSuperAdministrator
                && !$userEntry->isAdministrator
                && !$userEntry->trashed()
            );
        }

        $title = $this->title . ' Entry - ' . $formBuilder->name . ' # ' . $entry->id;

        return Inertia::render('FormBuilder::EntryDetail', $this->getData([
            'breadcrumbs' => [
                [
                    'title' => __('Forms'),
                    'url' => route('admin.form-builders.index'),
                ],
                [
                    'title' => 'Entries - ' . $formBuilder->name,
                    'url' => route('admin.form-builders.entries.index', $formBuilder->id)
                ],
                [
                    'title' => $title,
                ],
            ],
            'title' => $title,
            'formBuilder' => $formBuilder,
            'entry' => $formBuilderService->transformEntry($entry),
            'entryDisplay' => $formBuilderService->getComponentDisplayValues(
                $formBuilder->getFields(),
                $entry,
            ),
            'fieldLabels' => $formBuilder->getFieldLabelAndNames(),
            'can' => [
                'user' => [
                    'edit' => $user->can('user.edit'),
                    'redirectUser' => $canRedirectUser,
                ]
            ]
        ]));
    }

    public function bulkMarkAsRead(FormEntryMarkAsReadRequest $request, Form $formBuilder)
    {
        $this->formEntryService->markAsRead($formBuilder->id, $request->entries);

        $this->generateFlashMessage('The action ran successfully!');

        return to_route($this->baseRouteName.'.index', $this->pageParams($request));
    }

    public function bulkMarkAsUnread(FormEntryMarkAsReadRequest $request, Form $formBuilder)
    {
        $this->formEntryService->markAsUnread($formBuilder->id, $request->entries);

        $this->generateFlashMessage('The action ran successfully!');

        return to_route($this->baseRouteName.'.index', $this->pageParams($request));
    }

    public function bulkArchive(FormEntryArchiveRequest $request, FormBuilderService $formBuilderService, Form $formBuilder)
    {
        $this->formEntryService->archive($formBuilder->id, $request->entries);

        $paginator = $this->getPaginator($request, $formBuilderService);

        $this->generateFlashMessage('The action ran successfully!');

        return redirect()->route($this->baseRouteName.'.index',
            $this->pageParams($request, $paginator)
        );
    }

    public function bulkRestore(FormEntryArchiveRequest $request, FormBuilderService $formBuilderService, Form $formBuilder)
    {
        $this->formEntryService->restore($formBuilder->id, $request->entries);

        $paginator = $this->getPaginator($request, $formBuilderService);

        $this->generateFlashMessage('The action ran successfully!');

        return redirect()->route($this->baseRouteName.'.index',
            $this->pageParams($request, $paginator)
        );
    }

    public function bulkForceDelete(FormEntryArchiveRequest $request, FormBuilderService $formBuilderService, Form $formBuilder)
    {
        $this->formEntryService->forceDelete($formBuilder->id, $request->entries);

        $paginator = $this->getPaginator($request, $formBuilderService);

        $this->generateFlashMessage('The action ran successfully!');

        return redirect()->route($this->baseRouteName.'.index',
            $this->pageParams($request, $paginator)
        );
    }
}
