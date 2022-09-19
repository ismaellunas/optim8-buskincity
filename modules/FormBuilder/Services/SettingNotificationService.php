<?php

namespace Modules\FormBuilder\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\FormBuilder\Entities\FieldGroup;
use Modules\FormBuilder\Entities\FieldGroupNotificationSetting;
use Modules\FormBuilder\ModuleService;

class SettingNotificationService
{
    public function getRecords(
        string $formBuilderId,
        string $term = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $records = FieldGroupNotificationSetting::select([
                'id',
                'name',
                'subject',
                'is_active'
            ])
            ->orderBy('id', 'DESC')
            ->where('field_group_id', $formBuilderId)
            ->when($term, function ($query) use ($term) {
                $query->where('name', 'ILIKE', '%'.$term.'%')
                    ->orWhere('subject', 'ILIKE', '%'.$term.'%');
            })
            ->paginate($perPage);

        $this->transformRecords($records);

        return $records;
    }

    private function transformRecords($records)
    {
        $records->getCollection()->transform(function ($record) {
            $record->status = $this->getValueActiveOption($record->is_active);

            return $record;
        });
    }

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

    private function getValueActiveOption($id): string
    {
        $options = collect($this->getActiveOptions());

        return $options->where('id', $id)->value('value');
    }
}