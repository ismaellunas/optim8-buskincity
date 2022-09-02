<?php

namespace Modules\FormBuilder\Http\Controllers;

use App\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\FormBuilder\Services\SettingNotificationService;
use Modules\FormBuilder\Entities\FieldGroup;
use Modules\FormBuilder\Entities\FieldGroupNotificationSetting;

class SettingNotificationController extends CrudController
{
    protected $baseRouteName = 'admin.form-builders.settings.notifications';
    protected $recordsPerPage = 5;
    protected $title = 'Setting Notification';

    private $settingNotificationService;

    public function __construct(
        SettingNotificationService $settingNotificationService
    ) {
        $this->authorizeResource(FieldGroupNotificationSetting::class, 'notification');

        $this->settingNotificationService = $settingNotificationService;
    }

    public function records(Request $request)
    {
        return $this->settingNotificationService->getRecords(
            $request->term,
            $this->recordsPerPage,
        );
    }

    public function index()
    {
        // return view('formbuilder::index');
    }

    public function create()
    {
        // return view('formbuilder::create');
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

    public function destroy(
        FieldGroup $formBuilder,
        FieldGroupNotificationSetting $notification
    ) {
        $notification->delete();

        $this->generateFlashMessage('Notification setting deleted successfully!');

        return redirect()->back();
    }
}
