<?php

namespace Modules\FormBuilder\View\Components\Builder\Content;

use App\Services\SettingService;
use App\View\Components\Builder\Content\BaseContent;
use Modules\FormBuilder\ModuleService;
use Modules\FormBuilder\Services\FormBuilderService;

class FormBuilder extends BaseContent
{
    public $formId = null;
    public $recaptchaSiteKey = null;
    public $schema;
    public $form;
    public array $fieldGroups = [];
    public bool $isEnabled = false;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->isEnabled = app(ModuleService::class)->isModuleActive();

        if (! $this->isEnabled) {
            return;
        }

        $this->formId = $entity['config']['form']['id'] ?? null;
        $this->recaptchaSiteKey = $this->getRecaptchaSiteKey();

        $formBuilderService = app(FormBuilderService::class);
        $form = $formBuilderService->getForm($this->formId);

        if (
            !empty($form)
            && $form->canBeAccessed()
            && $formBuilderService->getFormLocation()->canBeAccessedBy()
        ) {
            $this->schema = $form->schema();
            $this->form = [];

            foreach ($this->schema['fieldGroups'] as $groupField) {

                if (! empty($groupField['fields'])) {

                    foreach ($groupField['fields'] as $key => $field) {
                        if (is_null($field['value'])) {
                            $this->form[ $key ] = null;
                        } else {
                            $this->form[ $key ] = $field['value'];
                        }
                    }
                }
            }
        }
    }

    private function getRecaptchaSiteKey(): ?string
    {
        $recaptchaKeys = app(SettingService::class)->getRecaptchaKeys();

        return $recaptchaKeys['recaptcha_site_key'] ?? null;
    }

    public function getColumnSizeClass($size)
    {
        if ($size) {
            return implode(' ', [
                $size . '-desktop',
                $size . '-tablet',
                'is-12-mobile',
            ]);
        }

        return 'is-12-mobile is-12-tablet is-12-desktop';
    }
}
