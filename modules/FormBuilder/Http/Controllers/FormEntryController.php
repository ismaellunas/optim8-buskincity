<?php

namespace Modules\FormBuilder\Http\Controllers;

use App\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormEntry;
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

    public function index(FormBuilderService $formBuilderService, Request $request, Form $formBuilder)
    {
        $title = 'Entries - ' . $formBuilder->name;

        $scopes = [];
        $scopes['read'] = $request->read;

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
            'records' => $formBuilderService->getEntryRecords(
                $formBuilder,
                $request->term,
                $scopes,
                $this->recordsPerPage,
            ),
            'readOptions' => $this->formEntryService->readOptions(),
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
}
