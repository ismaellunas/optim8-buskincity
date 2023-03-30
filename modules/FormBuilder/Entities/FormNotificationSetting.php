<?php

namespace Modules\FormBuilder\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormNotificationSetting extends BaseModel
{
    use HasFactory;

    public function saveFromInputs(array $inputs): void
    {
        $this->name = $inputs['name'];
        $this->send_to = $inputs['send_to'];
        $this->from_name = $inputs['from_name'];
        $this->from_email = $inputs['from_email'];
        $this->reply_to = $inputs['reply_to'];
        $this->bcc = $inputs['bcc'];
        $this->subject = $inputs['subject'];
        $this->message = $inputs['message'];
        $this->is_active = $inputs['is_active'];
        $this->form_id = $inputs['form_id'];
        $this->save();
    }
}
