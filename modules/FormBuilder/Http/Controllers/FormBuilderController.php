<?php

namespace Modules\FormBuilder\Http\Controllers;

use App\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\FormBuilder\Entities\FieldGroup;
use Modules\FormBuilder\Services\FormBuilderService;

class FormBuilderController extends CrudController
{
    protected $baseRouteName = 'admin.form-builders';
    protected $recordsPerPage = 15;
    protected $title = 'Form Builder';

    private $formBuilderService;

    public function __construct(FormBuilderService $formBuilderService)
    {
        $this->formBuilderService = $formBuilderService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        return Inertia::render('FormBuilder::Index', $this->getData([
            'can' => [
                'add' => $user->can('form_builder.add'),
                'edit' => $user->can('form_builder.edit'),
                'delete' => $user->can('form_builder.delete'),
            ],
            'records' => $this->formBuilderService->getRecords(
                $request->term,
                $this->recordsPerPage,
            ),
        ]));
    }

    public function create()
    {
        return Inertia::render('FormBuilder::Create', $this->getData([
            'title' => $this->getCreateTitle(),
        ]));
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        // return view('formbuilder::show');
    }

    public function edit($id)
    {
        // return view('formbuilder::edit');
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy(FieldGroup $formBuilder)
    {
        $formBuilder->delete();

        $this->generateFlashMessage('Form deleted successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }
}
