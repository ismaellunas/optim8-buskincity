import { isBlank } from './utils';

export function getTranslation(entity, locale) {
    let translation = entity.translations.find(translation => translation.locale === locale);

    if (!isBlank(translation)) {
        translation['settings'] = {};

        translation.page_translation_settings.forEach(function (setting) {
            translation['settings'][setting.key] = setting.value;
        });

        if (isBlank(translation['settings'])) {
            translation['settings'] = {
                template: null,
                background_color: null,
                page_height: null,
            };
        }
    }

    return translation;
}
