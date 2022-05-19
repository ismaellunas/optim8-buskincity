<?php

namespace App\Entities\Forms\Locations;

use App\Models\User;
use Illuminate\Support\Collection;

class UserProfileLocation
{
    public $entityId;

    protected $entity;
    private $savedResults;

    public function __construct($userId = null)
    {
        $this->entityId = $userId;

        if ($userId) {
            $this->entity = $this->getEntity($userId);
        }
    }

    protected function getEntity()
    {
        if (is_null($this->entity)) {
            $this->entity = User::find($this->entityId);
        }

        return $this->entity;
    }

    public function canBeAccessedBy(?User $author = null): bool
    {
        if (! $author) {
            return false;
        }

        $user = $this->getEntity();

        if (! $user) {
            return false;
        }

        if ($author->hasRole([config('permission.super_admin_role') ])) {
            return true;
        }

        if ($user->hasRole([config('permission.super_admin_role')])) {
            return false;
        }

        return ($user->id == $author->id);
    }

    public function canBeAccessedByEntity(array $locations = []): bool
    {
        return true;
    }

    public function save(Collection $fields, array $inputs)
    {
        $user = $this->getEntity();

        $storedValues = $this->getValues($fields->keys())->all();

        foreach ($fields as $field) {

            $storedValue = $field->findStoredValue($storedValues);

            if (! is_null($storedValue)) {
                $field->storedValue = $storedValue;
            }

            $field->entity = $user;

            $data = $field->getDataToBeSaved($inputs);

            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    $user->setMeta($key, $value);
                }
            }
        }

        $this->savedResults = $user->saveMetas();

        $this->saved($fields);
    }

    protected function saved(Collection $fields)
    {
        foreach ($fields as $field) {
            if (get_class($field) == 'App\Entities\Forms\Fields\File') {

                $meta = $this->savedResults->first(function ($meta) use ($field) {
                    return $meta->key == $field->name;
                });

                if ($meta) {
                    $field->setMedially($meta, $meta->value);
                }
            }
        }
    }

    public function getValues(Collection $keys): Collection
    {
        $user = $this->getEntity();

        return $user->getMetas($keys->all());
    }
}
