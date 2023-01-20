<?php

namespace Modules\FormBuilder\Services;

use App\Services\IPService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Modules\FormBuilder\Entities\Form as FormModel;
use Modules\FormBuilder\Entities\FormEntry;
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
        $records = FormModel::orderBy('id', 'DESC')
            ->when($term, function ($query) use ($term) {
                $query->where('name', 'ILIKE', '%'.$term.'%');
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
        FormModel $formBuilder,
        string $term = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $records = collect();

        $allFields = $this->getAllFields($formBuilder->fieldGroups);

        $fieldNames = $this->getDataFromFields($allFields, 'name');

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

            $record['id'] = $entry->id;

            foreach ($fieldNames as $fieldName) {
                $field = collect($allFields)->where('name', $fieldName)->first();

                $record[$fieldName] = $this->getDisplayValue(
                    $field,
                    $entry[$fieldName] ?? '-'
                );
            }

            $records->push($record);
        }

        return $records->paginate($perPage);
    }

    public function getWidgetEntryRecords(
        FormModel $formBuilder,
        int $perPage = 10
    ): LengthAwarePaginator {
        $records = collect();

        $allFields = $this->getAllFields($formBuilder->fieldGroups);

        $fieldNames = collect($this->getDataFromFields($allFields, 'name'))
            ->slice(0, 3)
            ->all();

        $entries = $formBuilder
            ->entries()
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($entries as $entry) {
            $record = [];

            $record['id'] = $entry->id;

            foreach ($fieldNames as $fieldName) {
                $field = collect($allFields)->where('name', $fieldName)->first();

                $record[$fieldName] = $this->getDisplayValue(
                    $field,
                    $entry[$fieldName] ?? '-'
                );
            }

            $records->push($record);
        }

        return $records->paginate($perPage);
    }

    public function getDataFromFields(
        array $fields,
        string $key
    ): array {
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

    public function getFieldLabelAndNames(array $fields): array
    {
        $fieldAndNames = [];

        foreach ($fields as $field) {
            $fieldAndNames[$field['name']] = $field['label']
                ?? Str::of($field['name'])->replace('_', ' ')->title();
        }

        return $fieldAndNames;
    }

    public function getFormOptions(): array
    {
        return FormModel::select([
                'key',
                'name'
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($field) {
                return [
                    'value' => $field->key,
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
        $model = FormModel::key($formId)->first();

        if ($model) {
            $className = $this->getFormClassName();

            $form = new $className($model);

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

    protected function getFieldClassName($type): string
    {
        return $this->fieldPath.'\\'.Str::studly($type);
    }

    public function saveValues($inputs): FormEntry
    {
        $values = $this->getSavedValues($inputs);

        $fieldGroupEntry = new FormEntry();

        $fieldGroupEntry->saveFromInputs($values);

        return $fieldGroupEntry;
    }

    public function getSavedValues(array $inputs): array
    {
        $values = [];

        $form = FormModel::with('fieldGroups')
            ->key($inputs['form_id'])
            ->first();

        if (!$form) {
            $this->abortAction();
        }

        foreach ($form->fieldGroups as $fieldGroup) {
            $fields = collect($fieldGroup->fields);

            foreach($inputs as $key => $value) {
                $fieldType = $fields->where('name', $key)->value('type');

                $fieldClassName = $this->getFieldClassName($fieldType);

                if (class_exists($fieldClassName)) {
                    $fieldClass = new $fieldClassName();

                    $values[$key] = $fieldClass->getSavedData($value);
                }
            }
        }

        if (Auth::check()) {
            $values['user_id'] = Auth::user()->id;
        }

        $ipService = app(IPService::class);
        $agent = new Agent();

        $agentBrowser = $agent->browser();
        $agentBrowserVersion = $agent->version($agentBrowser);

        $values['form_id'] = $form->id;
        $values['page_url'] = url()->previous() ?? null;
        $values['ip_address'] = $ipService->getClientIp();
        $values['timezone'] = $ipService->getTimezone();
        $values['browser'] = $agentBrowser . ($agentBrowserVersion ? ' version ' . $agentBrowserVersion : '');
        $values['device'] = $agent->device() . '; ' . $agent->platform();

        return $values;
    }

    public function swapTagWithEntryValue(FormEntry $entry, string $value = null): ?string
    {
        $swapLists = [];

        $allFields = $this->getAllFields($entry->form->fieldGroups);
        $entryValues = $this->getDisplayValues($allFields, $entry);

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

            if ($field) {
                $value = $this->getDisplayValue($field, $value);
            }

            $displayValues[$key] = $value;
        }

        return $displayValues;
    }

    public function getDisplayValue($field, $value): mixed
    {
        $className = $this->getFieldClassName($field['type']);

        if (class_exists($className)) {
            $fieldClass = new $className($field, $value);

            return $fieldClass->value();
        }

        return $value;
    }

    public function transformEntry($entry)
    {
        if (!empty($entry['user_id'])) {
            $entry->load('user');
            $entry->user->append('isSuperAdministrator');
        }

        return $entry->toArray();
    }

    public function getAllFields(Collection $fieldGroups): array
    {
        $fields = collect();

        foreach ($fieldGroups as $fieldGroup) {
            $fields = $fields->merge($fieldGroup->fields);
        }

        return $fields->all();
    }
}