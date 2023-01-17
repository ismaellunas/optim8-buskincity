import { isBlank } from './utils';
import { getEmptyPageTranslationSetting } from './page';

export function getTranslation(entity, locale, options = {}) {
    options = {
        ...{ isDefaultSettingsProvided: true },
        ...options
    }

    let translation = entity.translations.find(translation => translation.locale === locale);

    if (
        !isBlank(translation)
        && options.isDefaultSettingsProvided
        && isBlank(translation['settings'])
    ) {
        translation['settings'] = getEmptyPageTranslationSetting();
    }

    return translation;
}
