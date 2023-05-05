<?php

namespace Modules\FormBuilder\Entities;

use App\Models\Form as ModelForm;
use Illuminate\Database\Eloquent\Builder;

class Form extends ModelForm
{
    public const TYPE = 'form_builder';

    protected $attributes = [
        'type' => self::TYPE,
    ];

    protected function customNewQuery($newQuery): Builder
    {
        return $newQuery->type(self::TYPE);
    }

    protected static function newFactory()
    {
        return \Modules\FormBuilder\Database\factories\FormFactory::new();
    }

    public function saveFromInputs(array $inputs): void
    {
        $this->name = $inputs['name'];
        $this->setting = $inputs['setting'];
        $this->key = $inputs['form_id'];
        $this->save();
    }

    public function saveSettingFromInputs(array $inputs): void
    {
        $this->setting = $inputs;
        $this->save();
    }

    public function fieldGroups()
    {
        return $this->hasMany(FieldGroup::class, 'form_id');
    }

    public function entries()
    {
        return $this->hasMany(FormEntry::class, 'form_id');
    }

    public function notificationSettings()
    {
        return $this->hasMany(FormNotificationSetting::class, 'form_id');
    }

    public function mappingRules()
    {
        return $this->hasMany(FormMappingRule::class, 'form_id');
    }

    public function userCreationMappingRules()
    {
        return $this->mappingRules()->where('type', 'automate_user_creation');
    }

    public function activeNotificationSettings()
    {
        return $this->notificationSettings()->where('is_active', true);
    }
}
