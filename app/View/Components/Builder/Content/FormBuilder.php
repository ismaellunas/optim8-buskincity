<?php

namespace App\View\Components\Builder\Content;

use App\Services\ModuleService;
use App\Services\SettingService;
use Exception;

class FormBuilder extends BaseContent
{
    private $moduleName = 'FormBuilder';

    public $formId = null;
    public $recaptchaSiteKey = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        parent::__construct($entity);

        if (!$this->isModuleActive()) {
            throw new Exception(__('Form Builder module is not activated!'));
        }

        $this->formId = $entity['config']['form']['id'] ?? null;
        $this->recaptchaSiteKey = $this->getRecaptchaSiteKey();
    }

    private function isModuleActive(): bool
    {
        return app(ModuleService::class)->isModuleActive($this->moduleName);
    }

    private function getRecaptchaSiteKey(): ?string
    {
        $recaptchaKeys = app(SettingService::class)->getRecaptchaKeys();

        return $recaptchaKeys['recaptcha_site_key'] ?? null;
    }
}
