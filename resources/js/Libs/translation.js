export function getTranslation(entity, locale) {
    return entity.translations.find(translation => translation.locale === locale);
}
