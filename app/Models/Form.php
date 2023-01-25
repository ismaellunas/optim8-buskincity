<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'type',
    ];

    protected $casts = [
        'setting' => 'array',
    ];

    public function newQuery(bool $excludeDeleted = true)
    {
        return $this->customNewQuery(
            parent::newQuery($excludeDeleted)
        );
    }

    // Custom Method
    protected function customNewQuery($newQuery): Builder
    {
        return $newQuery->whereNull('type');
    }

    public function getFields(): collection
    {
        $fields = collect();

        foreach ($this->fieldGroups as $fieldGroup) {
            $fields = $fields->merge($fieldGroup->fields);
        }

        return $fields;
    }

    public function getFieldLabels(): array
    {
        $labels = $this->getDataFromFields('label');

        if (!$labels) {
            $labels = collect($this->getDataFromFields('name'))
                ->transform(function ($label) {
                    return Str::of($label)->replace('_', ' ')->title();
                })
                ->all();
        }

        return $labels;
    }

    public function getFieldNames(): array
    {
        return $this->getDataFromFields('name');
    }

    public function getFieldLabelAndNames(): array
    {
        $fieldAndNames = [];

        foreach ($this->getFields() as $field) {
            $fieldAndNames[$field['name']] = $field['label']
                ?? Str::of($field['name'])->replace('_', ' ')->title();
        }

        return $fieldAndNames;
    }

    private function getDataFromFields(string $key): array
    {
        $data = [];

        foreach ($this->getFields() as $field) {
            if (
                array_key_exists($key, $field)
                && isset($field[$key])
            ) {
                $data[] = $field[$key];
            }
        }

        return $data;
    }

    // Scope
    public function scopeKey($query, $key)
    {
        return $query->where('key', $key);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeLocationRoute($query, $locationRoute)
    {
        return $query->whereJsonContains('setting->locations', [ ['name' => $locationRoute] ]);
    }

    // Relation
    public function fieldGroups()
    {
        return $this->hasMany(FieldGroup::class, 'form_id');
    }
}
