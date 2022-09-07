<?php

namespace Modules\FormBuilder\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\FormBuilder\Entities\FieldGroup;

class FormBuilderService
{
    public function getRecords(
        string $term = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $records = FieldGroup::orderBy('id', 'DESC')
            ->when($term, function ($query) use ($term) {
                $query->where('name', 'ILIKE', '%'.$term.'%')
                    ->orWhere('title', 'ILIKE', '%'.$term.'%');
            })
            ->with(['entries.metas'])
            ->paginate($perPage);

        $this->transformRecords($records);

        return $records;
    }

    private function transformRecords($records)
    {
        $records->getCollection()->transform(function ($record) {
            $record->totalEntries = $record->entries->count();

            return $record;
        });
    }

    public function getEntryRecords(
        FieldGroup $formBuilder,
        string $term = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $records = collect();
        $fieldNames = $this->getDataFromFields($formBuilder->data['fields'], 'name');

        $entries = $formBuilder
            ->entries()
            ->whereHas('metas', function ($query) use ($term) {
                $query->where('value', 'ILIKE', '%'.$term.'%');
            })
            ->get();

        foreach ($entries as $entry) {
            $record = [];

            foreach ($fieldNames as $fieldName) {
                $record[$fieldName] = $entry[$fieldName] ?? null;
            }

            $records->push($record);
        }

        return $records->paginate($perPage);
    }

    public function getDataFromFields(array $fields, string $key): array
    {
        $data = [];

        foreach ($fields as $field) {
            if (
                array_key_exists($key, $field)
                && isset($field[$key])
            ) {
                $data[] = $field[$key];
            }
        }

        return $data;
    }

    public function getFormOptions(): array
    {
        return FieldGroup::select([
                'id',
                'name'
            ])
            ->get()
            ->map(function ($field) {
                return [
                    'value' => $field->id,
                    'name' => $field->name
                ];
            })
            ->all();
    }
}