<?php

namespace App\Services;

use App\Entities\Forms\Form;
use App\Models\{
    Form as FormModel,
    User,
};

class FormService
{
    private $formBasePath = 'App\\Entities\\Forms';

    public function getFormByName($name, User $author = null): ?Form
    {
        $model = FormModel::name($name)->first();

        if ($model) {
            $className = $this->getFormClassName($model->type);

            $form = new $className($model->id, $model->data, $author);

            $form->model = $model;

            return $form;
        }

        return null;
    }

    private function getFormClassName(?string $type): string
    {
        return $this->formBasePath."\\".$type.'Form';
    }
}
