<?php

namespace App\Console\Commands;

use App\Models\{
    FieldGroup,
    UserMeta,
};
use App\Services\{
    FormService,
    TranslationService
};
use Illuminate\Console\Command;

class FixUserMetaTranslation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:user-meta-translation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix data on user_meta for translation field.';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    private $formService;

    public function __construct(FormService $formService)
    {
        parent::__construct();

        $this->formService = $formService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $schemas = [];
        $models = FieldGroup::all();
        $locales = TranslationService::getLocales();

        foreach ($models as $model) {
            $data = $model->data;

            $schemas = array_merge(
                $schemas,
                $this->getSchemas($data['fields'])
            );
        }

        foreach ($schemas as $name => $schema) {
            $userMetas = UserMeta::where('key', $name)->get();

            foreach ($userMetas as $userMeta) {
                if (
                    isset($schema['is_translated'])
                    && $schema['is_translated'] == true
                ) {
                    $value = $userMeta['value'];

                    if (!is_array($value)) {
                        $newValue['en'] = $value;

                        foreach ($locales as $locale) {
                            if ($locale != 'en') {
                                $newValue[$locale] = null;
                            }
                        }

                        $userMeta['value'] = $newValue;
                        $userMeta['type'] = 'array';
                    }

                    $userMeta->save();
                }
            }
        }

        return 0;
    }

    private function getFieldClassName($type): string
    {
        return "\\App\\Entities\\Forms\\Fields\\".$type;
    }

    private function getSchemas($fields)
    {
        $schemaCollection = collect();

        foreach ($fields as $name => $field) {
            $className = $this->getFieldClassName($field['type']);

            if (class_exists($className)) {
                $fieldObject = new $className($name, $field);

                $schemaCollection->put($name, $fieldObject->schema());
            }
        }

        return $schemaCollection->all();
    }
}
