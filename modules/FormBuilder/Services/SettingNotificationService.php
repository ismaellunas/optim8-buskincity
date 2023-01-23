<?php

namespace Modules\FormBuilder\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormNotificationSetting;
use Modules\FormBuilder\ModuleService;

class SettingNotificationService
{
    public function getRecords(
        string $formBuilderId,
        string $term = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $records = FormNotificationSetting::select([
                'id',
                'name',
                'subject',
                'is_active'
            ])
            ->orderBy('id', 'DESC')
            ->where('form_id', $formBuilderId)
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

    public function getFieldNameOptions(Form $form): array
    {
        $fieldNames = [];

        foreach ($form->fieldGroups as $fieldGroup) {
            foreach ($fieldGroup->fields as $field) {
                if (
                    array_key_exists('name', $field)
                    && isset($field['name'])
                ) {
                    $fieldNames[] = [
                        'id' => $field['name'],
                        'value' => $field['label'] ?? Str::of($field['name'])->replace('_', ' ')->title(),
                    ];
                }
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