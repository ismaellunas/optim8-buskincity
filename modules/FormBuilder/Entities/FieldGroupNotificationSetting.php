<?php

namespace Modules\FormBuilder\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FieldGroupNotificationSetting extends Model
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
        $this->field_group_id = $inputs['field_group_id'];
        $this->save();
    }
}
