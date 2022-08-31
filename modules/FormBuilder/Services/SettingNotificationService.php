<?php

namespace Modules\FormBuilder\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\FormBuilder\Entities\FieldGroup;
use Modules\FormBuilder\Entities\FieldGroupNotificationSetting;
use Modules\FormBuilder\ModuleService;

class SettingNotificationService
{
    public function getRecords(
        string $term = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        return FieldGroupNotificationSetting::select([
                'id',
                'name',
                'subject'
            ])
            ->orderBy('id', 'DESC')
            ->when($term, function ($query) use ($term) {
                $query->where('name', 'ILIKE', '%'.$term.'%')
                    ->orWhere('subject', 'ILIKE', '%'.$term.'%');
            })
            ->paginate($perPage);
    public function getFieldNameOptions(FieldGroup $fieldGroup): array
    {
        $fieldNames = [];
        $fields = $fieldGroup->data['fields'];

        foreach ($fields as $field) {
            if (
                array_key_exists('name', $field)
                && isset($field['name'])
            ) {
                $fieldNames[] = [
                    'id' => $field['name'],
                    'value' => $field['label']
                ];
            }
        }

        return $fieldNames;
    }

    public function getActiveOptions(): array
    {
        return ModuleService::activeOptions();
    }
}