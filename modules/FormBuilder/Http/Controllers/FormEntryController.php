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

        if ($request->page > $paginator->lastPage()) {
            return redirect()->route($this->baseRouteName.'.index',
                $this->pageParams($request, $paginator)
            );
        }

        return Inertia::render('FormBuilder::Entries', $this->getData([
            'breadcrumbs' => [
                [
                    'title' => __('Form Builders'),
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
                'forceDelete' => $user->can('delete', $formBuilder),
            ],
            'i18n' => [
                'entries' => __('Entries'),
                'archived' => __('Archived'),
                'search' => __('Search'),
                'filter' => __('Filter'),
                'actions' => __('Actions'),
                'mark_as_read' => __('Mark as read'),
                'mark_as_unread' => __('Mark as unread'),
                'archive' => __('Archive'),
                'restore' => __('Restore'),
                'delete' => __('Delete'),
                'data_is_empty' => __('Data is empty'),
                'confirm_archive' => __('Confirm archive'),
                'are_you_sure' => __('Are you sure?'),
                'confirm_restore' => __('Confirm restore'),
                'confirm_deletion' => __('Confirm deletion'),
                'confirm_deletion_message' => __('Once the resources are deleted, they will be permanently deleted.'),
            ],
        ]));
    }

    public function show(FormBuilderService $formBuilderService, Form $formBuilder, FormEntry $formEntry)
    {
        $user = auth()->user();
        $userEntry = $formEntry->user;

        $canRedirectUser = false;

        if ($userEntry) {
            $canRedirectUser = (
                !$userEntry->isSuperAdministrator
                && !$userEntry->isAdministrator
                && !$userEntry->trashed()
            );
        }

        $title = $this->title . ' Entry - ' . $formBuilder->name . ' # ' . $formEntry->id;

        return Inertia::render('FormBuilder::EntryDetail', $this->getData([
            'breadcrumbs' => [
                [
                    'title' => __('Form Builders'),
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
            'entry' => $this->formEntryService->transformEntry($formEntry, $user),
            'entryDisplay' => $formBuilderService->getComponentDisplayValues(
                $formBuilder->getFields(),
                $formEntry,
            ),
            'fieldLabels' => $formBuilder->getFieldLabelAndNames(),
            'can' => [
                'user' => [
                    'edit' => $user->can('user.edit'),
                    'redirectUser' => $canRedirectUser,
                ],
                'automate_user_creation' => $user->can('automateUserCreation', $formEntry),
            ],
            'i18n' => [
                'entry' => __('Entry'),
                'entry_id' => __('Entry ID'),
                'user_ip' => __('User IP'),
                'user' => __('User'),
                'submitted_on' => __('Submitted on'),
                'timezone' => __('Timezone'),
                'page_url' => __('Page URL'),
                'browser' => __('Browser'),
                'device' => __('Device'),
                'actions' => __('Actions'),
                'mark_as_read' => __('Mark as read'),
                'mark_as_unread' => __('Mark as unread'),
                'archive' => __('Archive'),
                'restore' => __('Restore'),
                'delete' => __('Delete'),
                'confirm_archive' => __('Confirm archive'),
                'are_you_sure' => __('Are you sure?'),
                'confirm_restore' => __('Confirm restore'),
                'confirm_deletion' => __('Confirm deletion'),
                'confirm_deletion_message' => __('Once the resources are deleted, they will be permanently deleted.'),
                'create_or_update_user' => __('Create or update :resource', ['resource' => __('User')]),
            ],
        ]));
    }

    private function afterAction()
    {
        $this->generateFlashMessage('The action ran successfully!');

        return back();
    }

    private function afterBulkAction(Request $request)
    {
        $this->generateFlashMessage('The action ran successfully!');

        return to_route($this->baseRouteName.'.index', $this->pageParams($request));
    }

    private function afterBulkDangerAction(Request $request, FormBuilderService $formBuilderService)
    {
        $paginator = $this->getPaginator($request, $formBuilderService);

        $this->generateFlashMessage('The action ran successfully!');

        return redirect()->route($this->baseRouteName.'.index',
            $this->pageParams($request, $paginator)
        );
    }

    public function bulkMarkAsRead(FormEntryMarkAsReadRequest $request, Form $formBuilder)
    {
        $this->formEntryService->markAsRead($formBuilder->id, $request->entries);

        return $this->afterBulkAction($request);
    }

    public function bulkMarkAsUnread(FormEntryMarkAsReadRequest $request, Form $formBuilder)
    {
        $this->formEntryService->markAsUnread($formBuilder->id, $request->entries);

        return $this->afterBulkAction($request);
    }

    public function bulkArchive(FormEntryArchiveRequest $request, FormBuilderService $formBuilderService, Form $formBuilder)
    {
        $this->formEntryService->archive($formBuilder->id, $request->entries);

        return $this->afterBulkDangerAction($request, $formBuilderService);
    }

    public function bulkRestore(FormEntryArchiveRequest $request, FormBuilderService $formBuilderService, Form $formBuilder)
    {
        $this->formEntryService->restore($formBuilder->id, $request->entries);

        return $this->afterBulkDangerAction($request, $formBuilderService);
    }

    public function bulkForceDelete(FormEntryArchiveRequest $request, FormBuilderService $formBuilderService, Form $formBuilder)
    {
        $this->formEntryService->forceDelete($formBuilder->id, $request->entries);

        return $this->afterBulkDangerAction($request, $formBuilderService);
    }

    public function markAsRead(FormEntryMarkAsReadRequest $request, Form $formBuilder, FormEntry $formEntry)
    {
        $this->formEntryService->markAsRead($formBuilder->id, [$formEntry->id]);

        return $this->afterAction();
    }

    public function markAsUnread(FormEntryMarkAsReadRequest $request, Form $formBuilder, FormEntry $formEntry)
    {
        $this->formEntryService->markAsUnread($formBuilder->id, [$formEntry->id]);

        return $this->afterAction();
    }

    public function archive(FormEntryArchiveRequest $request, Form $formBuilder, FormEntry $formEntry)
    {
        $this->formEntryService->archive($formBuilder->id, [$formEntry->id]);

        return $this->afterAction();
    }

    public function restore(FormEntryArchiveRequest $request, Form $formBuilder, FormEntry $formEntry)
    {
        $this->formEntryService->restore($formBuilder->id, [$formEntry->id]);

        return $this->afterAction();
    }

    public function forceDelete(FormEntryArchiveRequest $request, Form $formBuilder, FormEntry $formEntry)
    {
        $this->formEntryService->forceDelete($formBuilder->id, [$formEntry->id]);

        $this->generateFlashMessage('The action ran successfully!');

        return to_route($this->baseRouteName.'.index', [
            'form_builder' => $formBuilder->id,
            'tab' => 'archived'
        ]);
    }
}
