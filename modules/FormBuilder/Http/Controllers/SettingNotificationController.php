<?php

namespace Modules\FormBuilder\Http\Controllers;

use App\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\FormBuilder\Services\SettingNotificationService;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormNotificationSetting;
use Modules\FormBuilder\Http\Requests\SettingNotificationRequest;

class SettingNotificationController extends CrudController
{
    protected $baseRouteName = 'admin.form-builders.settings.notifications';
    protected $recordsPerPage = 10;
    protected $title = 'Setting Notification';

    private $settingNotificationService;

    public function __construct(
        SettingNotificationService $settingNotificationService
    ) {
        $this->authorizeResource(FormNotificationSetting::class, 'form_builder');

        $this->settingNotificationService = $settingNotificationService;
    }

    public function records(Request $request, Form $formBuilder)
    {
        return $this->settingNotificationService->getRecords(
            $formBuilder->id,
            $request->term,
            $this->recordsPerPage,
        );
    }

    public function create(Form $formBuilder)
    {
        return Inertia::render('FormBuilder::Settings/Notification/Create', $this->getData([
            'activeOptions' => $this->settingNotificationService->getActiveOptions(),
            'breadcrumbs' => [
                [
                    'title' => __('Form Builders'),
                    'url' => route('admin.form-builders.index'),
                ],
                [
                    'title' => __('Edit Form Builder'),
                    'url' => route('admin.form-builders.edit', $formBuilder->id),
                ],
                [
                    'title' => $this->getCreateTitle(),
                ],
            ],
            'title' => $this->getCreateTitle(),
            'formBuilder' => $formBuilder,
            'fieldNotes' => $this->fieldNotes(),
            'fieldNameOptions' => $this->settingNotificationService
                ->getFieldNameOptions($formBuilder),
            'i18n' => $this->translationCreateEditPage(),
        ]));
    }

    public function store(SettingNotificationRequest $request, Form $formBuilder)
    {
        $inputs = $request->validated();
        $inputs['form_id'] = $formBuilder->id;

        $notificationSetting = new FormNotificationSetting();

        $notificationSetting->saveFromInputs($inputs);

        $this->generateFlashMessage('The :resource was created!', [
            'resource' => __('Notification setting')
        ]);

        return redirect()->route($this->baseRouteName . '.edit', [
            'form_builder' => $formBuilder->id,
            'notification' => $notificationSetting->id
        ]);
    }

    public function edit(
        Form $formBuilder,
        FormNotificationSetting $notification
    ) {
        $notification->send_to = $this->convertJsonToString($notification->send_to);
        $notification->bcc = $this->convertJsonToString($notification->bcc);

        return Inertia::render('FormBuilder::Settings/Notification/Edit', $this->getData([
            'activeOptions' => $this->settingNotificationService->getActiveOptions(),
            'breadcrumbs' => [
                [
                    'title' => __('Form Builders'),
                    'url' => route('admin.form-builders.index'),
                ],
                [
                    'title' => __('Edit Form Builder'),
                    'url' => route('admin.form-builders.edit', $formBuilder->id),
                ],
                [
                    'title' => $this->getEditTitle(),
                ],
            ],
            'title' => $this->getEditTitle(),
            'formBuilder' => $formBuilder,
            'settingNotification' => $notification,
            'fieldNotes' => $this->fieldNotes(),
            'fieldNameOptions' => $this->settingNotificationService
                ->getFieldNameOptions($formBuilder),
            'i18n' => $this->translationCreateEditPage(),
        ]));
    }

    public function update(
        SettingNotificationRequest $request,
        Form $formBuilder,
        FormNotificationSetting $notification
    ) {
        $inputs = $request->validated();
        $inputs['form_id'] = $formBuilder->id;

        $notification->saveFromInputs($inputs);

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Notification setting')
        ]);

        return redirect()->route($this->baseRouteName . '.edit', [
            'form_builder' => $formBuilder->id,
            'notification' => $notification->id
        ]);
    }

    public function destroy(
        Form $formBuilder,
        FormNotificationSetting $notification
    ) {
        $notification->delete();

        $this->generateFlashMessage('The :resource was deleted!', [
            'resource' => __('Notification setting')
        ]);

        return redirect()->back();
    }

    private function fieldNotes(): array
    {
        return [
            'send_to' => __('Enter the email address you would like the notification email sent to.'),
            'from_name' => __('Enter the name you would like the notification email sent from, or select the name from available name fields.'),
            'from_email' => __('Enter an authorized email address you would like the notification email sent from. To avoid deliverability issues, always use your site domain in the from email.'),
            'reply_to' => __('Enter the email address you would like to be used as the reply to address for the notification email.'),
            'bcc' => __('Enter a comma separated list of email addresses you would like to receive a BCC of the notification email.')
        ];
    }

    private function convertJsonToString($jsonValue): string
    {
        return implode(",", json_decode($jsonValue));
    }

    private function translationCreateEditPage(): array
    {
        return [
            'details' => __('Details'),
            'name' => __('Name'),
            'send_to_email' => __('Send to email'),
            'from_name' => __('From name'),
            'from_email' => __('From email'),
            'reply_to' => __('Reply to'),
            'bcc' => __('Bcc'),
            'subject' => __('Subject'),
            'message' => __('Message'),
            'options' => __('Options'),
            'is_activated' => __('Is activated?'),
            'cancel' => __('Cancel'),
            'create' => __('Create'),
            'update' => __('Update'),
        ];
    }
}
