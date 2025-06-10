<?php

namespace Modules\FormBuilder\Shortcodes;

use App\Services\SettingService;
use Modules\FormBuilder\ModuleService;
use Modules\FormBuilder\Services\FormBuilderService;

class FormBuilder
{
    public static string $name = 'form-builder';

    public function register($shortcode)
    {
        if (! app(ModuleService::class)->isModuleActive()) {
            return "";
        }

        $formBuilderService = app(FormBuilderService::class);
        $formBuilder = $formBuilderService->getForm($shortcode->form_id);
        $schema = null;
        $form = [];
        if (
            !empty($formBuilder)
            && $formBuilder->canBeAccessed()
            && $formBuilderService->getFormLocation()->canBeAccessedBy()
        ) {
            $schema = $formBuilder->schema();

            foreach ($schema['fieldGroups'] as $groupField) {

                if (! empty($groupField['fields'])) {

                    foreach ($groupField['fields'] as $key => $field) {
                        if (is_null($field['value'])) {
                            $form[ $key ] = null;
                        } else {
                            $form[ $key ] = $field['value'];
                        }
                    }
                }
            }
        }

        $recaptchaKeys = app(SettingService::class)->getRecaptchaKeys();
        $recaptchaSiteKey = $recaptchaKeys['recaptcha_site_key'] ?? null;

        if ($schema == null) {
            return "";
        }

        return view('formbuilder::form-builder-slotable-slot', [
            'schema' => $schema,
            'formId' => $shortcode->form_id,
            'recaptchaSiteKey' => $recaptchaSiteKey,
            'form' => $form,
            'getColumnSizeClass' => function ($size): string {
                if ($size) {
                    return implode(' ', [
                        $size . '-desktop',
                        $size . '-tablet',
                        'is-12-mobile',
                    ]);
                }
                return 'is-12-mobile is-12-tablet is-12-desktop';
            }
        ])->render();
    }
}
