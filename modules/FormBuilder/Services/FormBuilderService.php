<?php

namespace Modules\FormBuilder\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\FormBuilder\Entities\FieldGroup;
use Modules\FormBuilder\Entities\FieldGroupEntry;
use Modules\FormBuilder\Forms\Form;
use Symfony\Component\HttpFoundation\Response;

class FormBuilderService
{
    private $formBasePath = 'Modules\\FormBuilder\\Forms';
    private $formLocationBasePath = "Modules\\FormBuilder\\Forms\\Locations";
    private $fieldPath = "Modules\\FormBuilder\\Fields";

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
        $fields = $formBuilder->data['fields'];
        $fieldNames = $this->getDataFromFields($fields, 'name');

        $entries = $formBuilder
            ->entries()
            ->whereHas('metas', function ($query) use ($term) {
                $query->when($term, function ($q) use ($term) {
                    $q->where('value', 'ILIKE', '%'.$term.'%');
                });
            })
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($entries as $entry) {
            $record = [];

            foreach ($fieldNames as $fieldName) {
                $field = collect($fields)->where('name', $fieldName)->first();

                $record[$fieldName] = $this->getDisplayValue(
                    $field,
                    $entry[$fieldName] ?? '-'
                );
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

    public function getFieldLabels(array $fields): array
    {
        $labels = $this->getDataFromFields($fields, 'label');

        if (!$labels) {
            $labels = collect($this->getDataFromFields($fields, 'name'))
                ->transform(function ($label) {
                    return Str::of($label)->replace('_', ' ')->title();
                })
                ->all();
        }

        return $labels;
    }

    public function getFormOptions(): array
    {
        return FieldGroup::select([
                'title',
                'name'
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($field) {
                return [
                    'value' => $field->title,
                    'name' => $field->name
                ];
            })
            ->all();
    }

    public function getFormLocation()
    {
        $className = $this->formLocationBasePath.'\\'.'GuestLocation';

        return new $className();
    }

    private function getFormClassName(): string
    {
        return $this->formBasePath."\\".'Form';
    }

    public function getForm(?string $formId): ?Form
    {
        $model = FieldGroup::formId($formId)->first();

        if ($model) {
            $className = $this->getFormClassName();
            $data = $model->data;
            $data['settings'] = $model->settings;

            $form = new $className($model->id, $data);

            if ($form->canBeAccessedByLocation()) {
                $form->model = $model;

                return $form;
            }
        }

        return null;
    }

    public function abortAction(): void
    {
        abort(Response::HTTP_FORBIDDEN);
    }

    public function transformInputs(&$inputs): void
    {
        $fieldGroupId = FieldGroup::where('title', $inputs['form_id'])->value('id');

        if (!$fieldGroupId) {
            $this->formBuilderService->abortAction();
        }

        if (Auth::check()) {
            $inputs['user_id'] = Auth::user()->id;
        }

        $inputs['field_group_id'] = $fieldGroupId;
        unset($inputs['form_id']);
    }

    public function swapTagWithEntryValue(FieldGroupEntry $entry, string $value = null): ?string
    {
        $swapLists = [];
        $fields = $entry->fieldGroup->data['fields'];
        $entryValues = $this->getDisplayValues($fields, $entry);

        foreach ($entryValues as $key => $entryValue) {
            $swapLists['{'.$key.'}'] = $entryValue;
        }

        return Str::swap($swapLists, $value);
    }

    public function getDisplayValues($fields, $entry)
    {
        $displayValues = [];
        $fields = collect($fields);
        $entryValues = $entry->metas
            ->pluck('value', 'key')
            ->toArray();

        foreach ($entryValues as $key => $entryValue) {
            $value = $entryValue;
            $field = $fields->where('name', $key)->first();

            $displayValues[$key] = $this->getDisplayValue($field, $value);
        }

        return $displayValues;
    }

    public function getDisplayValue($field, $value): mixed
    {
        $className = $this->fieldPath.'\\'.Str::studly($field['type']);

        if (class_exists($className)) {
            $fieldClass = new $className($field, $value);

            return $fieldClass->value();
        }

        return $value;
    }
}