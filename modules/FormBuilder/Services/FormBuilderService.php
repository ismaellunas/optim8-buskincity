<?php

namespace Modules\FormBuilder\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\FormBuilder\Entities\FieldGroup;
use Symfony\Component\HttpFoundation\Response;

class FormBuilderService
{
    private $formBasePath = 'App\\Entities\\Forms';
    private $formLocationBasePath = "Modules\\FormBuilder\\Forms\\Locations";

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

    public function getSchema(
        string $formId,
    ):array {
        $formLocation = $this->getFormLocation();

        $form = $this->getForm($formId);

        if (!$formLocation->canBeAccessedBy()) {
            $this->abortAction();
        }

        if ($form->canBeAccessed()) {
            return $form->schema();
        }

        return null;
    }

    private function getFormLocation()
    {
        $className = $this->formLocationBasePath.'\\'.'GuestLocation';

        return new $className();
    }

    private function getFormClassName(?string $type = null): string
    {
        return $this->formBasePath."\\".$type.'Form';
    }

    public function getForm(string $formId)
    {
        $model = FieldGroup::where('title', $formId)->first();

        if ($model) {
            $className = $this->getFormClassName();

            $form = new $className($model->id, $model->data);

            if ($form->canBeAccessedByLocation()) {
                $form->model = $model;

                return $form;
            }
        }

        return null;
    }

    private function abortAction(): void
    {
        abort(Response::HTTP_FORBIDDEN);
    }
}