import { isBlank } from './utils';
import { getEmptyPageTranslationSetting } from './page';

export function getTranslation(entity, locale) {
    let translation = entity.translations.find(translation => translation.locale === locale);

    if (!isBlank(translation) && isBlank(translation['settings'])) {
        translation['settings'] = getEmptyPageTranslationSetting();
    }

    return translation;
}
