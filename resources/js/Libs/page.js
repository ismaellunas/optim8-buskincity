export function getEmptyPageTranslation() {
    return {
        id: null,
        title: null,
        slug: null,
        excerpt: null,
        data: {"structures": [], "entities": {}, "media": []},
        meta_description: null,
        meta_title: null,
        status: 0,
        settings: getEmptyPageTranslationSetting(),
    };
}

export function getEmptyPageTranslationSetting() {
    return {
        layout: null,
        main_background_color: null,
        height: null,
    };
}
