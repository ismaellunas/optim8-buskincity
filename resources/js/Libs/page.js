export function getEmptyPageTranslation(options) {
    options = {
        ...{ isDefaultSettingsProvided: true },
        ...options
    };

    const pageTranslation = {
        id: null,
        title: null,
        slug: null,
        excerpt: null,
        data: {"structures": [], "entities": {}, "media": []},
        meta_description: null,
        meta_title: null,
        status: 0,
    };

    if (options.isDefaultSettingsProvided) {
        pageTranslation.settings = getEmptyPageTranslationSetting();
    }

    return pageTranslation;
}

export function getEmptyPageTranslationSetting() {
    return {
        layout: null,
        main_background_color: null,
        height: null,
    };
}
