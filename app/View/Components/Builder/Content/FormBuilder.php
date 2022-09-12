<?php

namespace App\View\Components\Builder\Content;

use App\Services\ModuleService;
use Modules\FormBuilder\Entities\FieldGroup;
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

        $this->recaptchaSiteKey = config('constants.recaptcha_site_key');
    }

    private function isModuleActive(): bool
    {
        return app(ModuleService::class)->isModuleActive($this->moduleName);
    }
}
