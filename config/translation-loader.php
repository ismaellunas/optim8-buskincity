<?php

return [

    /*
     * Language lines will be fetched by these loaders. You can put any class here that implements
     * the Spatie\TranslationLoader\TranslationLoaders\TranslationLoader-interface.
     */
    'translation_loaders' => [
        \App\Models\Translation::class,
    ],

    /*
     * This is the model used by the Db Translation loader. You can put any model here
     * that extends Spatie\TranslationLoader\LanguageLine.
     */
    'model' => null,

    /*
     * This is the translation manager which overrides the default Laravel `translation.loader`
     */
    'translation_manager' => Spatie\TranslationLoader\TranslationLoaderManager::class,

];
