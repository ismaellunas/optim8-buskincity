<?php

namespace Modules\FormBuilder\Entities;

use App\Models\FieldGroup as ModelFieldGroup;
use Illuminate\Database\Eloquent\Builder;

class FieldGroup extends ModelFieldGroup
{
    public const TYPE = 'form_builder';

    protected function customNewQuery($newQuery): Builder
    {
        return $newQuery->type(self::TYPE);
    }

    protected static function newFactory()
    {
        return \Modules\FormBuilder\Database\factories\FieldGroupFactory::new();
    }

    public function scopeFormId($query, $formId)
    {
        return $query->where('title', $formId);
    }

    public function saveFromInputs(array $inputs): void
    {
        $this->name = $inputs['name'];
        $this->data = $inputs['builders'];
        $this->title = $inputs['form_id'];
        $this->type = self::TYPE;
        $this->save();
    }

    public function entries()
    {
        return $this->hasMany(FieldGroupEntry::class, 'field_group_id');
    }

    public function notificationSettings()
    {
        return $this->hasMany(FieldGroupNotificationSetting::class, 'field_group_id');
    }

    public function activeNotificationSettings()
    {
        return $this->notificationSettings()->where('is_active', true);
    }

    public function settings()
    {
        return $this->hasMany(FieldGroupSetting::class, 'field_group_id');
    }
}
