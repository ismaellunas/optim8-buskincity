<?php

namespace Modules\FormBuilder\Http\Controllers;

use App\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\FormBuilder\Services\SettingNotificationService;
use Modules\FormBuilder\Entities\FieldGroup;
use Modules\FormBuilder\Entities\FieldGroupNotificationSetting;
use Modules\FormBuilder\Http\Requests\SettingNotificationRequest;

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

    public function create(FieldGroup $formBuilder)
    {
        return Inertia::render('FormBuilder::Settings/NotificationCreate', $this->getData([
            'activeOptions' => $this->settingNotificationService->getActiveOptions(),
            'title' => $this->getCreateTitle(),
            'formBuilder' => $formBuilder,
            'fieldNotes' => $this->fieldNotes(),
            'fieldNameOptions' => $this->settingNotificationService
                ->getFieldNameOptions($formBuilder),
        ]));
    }

    public function store(SettingNotificationRequest $request, FieldGroup $formBuilder)
    {
        $inputs = $request->validated();
        $inputs['field_group_id'] = $formBuilder->id;

        $notificationSetting = new FieldGroupNotificationSetting();

        $notificationSetting->saveFromInputs($inputs);

        $this->generateFlashMessage('Form created successfully!');

        return redirect()->route($this->baseRouteName . '.edit', [
            'form_builder' => $formBuilder->id,
            'notification' => $notificationSetting->id
        ]);
    }

    public function edit(
        FieldGroup $formBuilder,
        FieldGroupNotificationSetting $notification
    ) {
        $notification->send_to = $this->convertJsonToString($notification->send_to);
        $notification->bcc = $this->convertJsonToString($notification->bcc);

        return Inertia::render('FormBuilder::Settings/NotificationEdit', $this->getData([
            'activeOptions' => $this->settingNotificationService->getActiveOptions(),
            'title' => $this->getEditTitle(),
            'formBuilder' => $formBuilder,
            'settingNotification' => $notification,
            'fieldNotes' => $this->fieldNotes(),
            'fieldNameOptions' => $this->settingNotificationService
                ->getFieldNameOptions($formBuilder),
        ]));
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

    private function fieldNotes(): array
    {
        return [
            'send_to' => 'Enter the email address you would like the notification email sent to.',
            'from_name' => 'Enter the name you would like the notification email sent from, or select the name from available name fields.',
            'from_email' => 'Enter an authorized email address you would like the notification email sent from. To avoid deliverability issues, always use your site domain in the from email.',
            'reply_to' => 'Enter the email address you would like to be used as the reply to address for the notification email.',
            'bcc' => 'Enter a comma separated list of email addresses you would like to receive a BCC of the notification email.'
        ];
    }

    private function convertJsonToString($jsonValue): string
    {
        return implode(",", json_decode($jsonValue));
    }
}
