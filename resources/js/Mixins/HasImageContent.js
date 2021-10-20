import { isArray } from 'lodash';
import { isBlank } from '@/Libs/utils';

export default {
    data() {
        return {
            entityImage: {
                mediaId: null,
            },
            images: null,
        }
    },

    methods: {
        getImageFromPageImages(pageImages, locale, mediaId) {
            const localeImages = (
                isArray(pageImages)
                ? pageImages
                : pageImages[locale]
            );

            return localeImages.find(image => {
                return image.id === mediaId;
            });
        },
    },

    computed: {
        hasImage() {
            return (!isBlank(this.entityImage.mediaId));
        },

        imageSrc() {
            const mediaId = this.entityImage.mediaId;
            if (mediaId) {
                const image = this.getImageFromPageImages(
                    this.images,
                    this.selectedLocale,
                    mediaId
                );

                if (image) {
                    return image.file_url;
                }
            }
            return "";
        },

        altText() {
            const mediaId = this.entityImage.mediaId;
            if (mediaId) {
                const image = this.getImageFromPageImages(
                    this.images,
                    this.selectedLocale,
                    mediaId
                );

                if (image?.translations) {
                    const translation = (
                        image.translations.find(trans => trans.locale === this.selectedLocale)
                        ?? image.translations[0]
                    );
                    return translation?.alt;
                }
            }
            return "";
        },
    },
};
