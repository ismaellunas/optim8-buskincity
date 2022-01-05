<?php

namespace App\Services;

use App\Entities\Forms\Form;
use App\Models\Form as FormModel;

class FormService
{
    private $formBasePath = 'App\\Entities\\Forms';

    public function getFormByName($name): ?Form
    {
        $model = FormModel::where('name', $name)->first();

        if ($model) {
            $className = $this->getFormClassName($model->type);

            return new $className($model->id, $model->data);
        }

        return null;
    }

    private function getFormClassName(?string $type): string
    {
        return $this->formBasePath."\\".$type.'Form';
    }
}
