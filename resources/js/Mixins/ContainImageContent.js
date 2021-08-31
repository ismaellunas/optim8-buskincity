import { isArray, remove } from 'lodash';
import { isBlank } from '@/Libs/utils';
import { oops as oopsAlert } from '@/Libs/alert';

export default {
    setup() {
        return {
            pageMedia: [],
        };
    },
    data() {
        return {
            entityImage: {
                mediaId: null,
            },
            images: null,
        }
    },
    methods: {
        attachImageToMedia(imageId, media) {
            const existingMedia = media.find(media => media.id === imageId);

            if (existingMedia) {
                existingMedia.numberOfUsage++;
            } else {
                media.push({
                    id: imageId,
                    is_image: true,
                    numberOfUsage: 1,
                });
            }
        },
        detachImageFromMedia(imageId, media) {
            const existingMedia = media.find(media => media.id === imageId);

            if (existingMedia) {
                existingMedia.numberOfUsage--;

                if (existingMedia.numberOfUsage === 0) {
                    remove(media, function (medium) {
                        return medium.id == existingMedia.id;
                    });
                }
            }
        },
        selectImage(image) {
            const locale = this.selectedLocale;
            if (!isBlank(this.entityImage.mediaId)) {
                this.detachImageFromMedia(this.entityImage.mediaId, this.pageMedia);
            }

            if (!this.images[ locale ]) {
                this.images[ locale ] = [];
            }
            this.images[locale].push(image);

            this.entityImage.mediaId = image.id;

            this.attachImageToMedia(image.id, this.pageMedia);

            this.onImageSelected();
        },
        updateImage(response) {
            this.selectImage(response.data);
            this.onImageUpdated();
        },
        getImagesList(url) {
            const self = this;
            axios.get(url)
                .then(function (response) {
                    self.onImageListLoadedSuccess(response.data)
                })
                .catch(function (error) {
                    oopsAlert();
                    self.onImageListLoadedFail(error);
                });
        },
        onImageSelected() {},
        onImageUpdated() {},
        onImageListLoadedSuccess(data) {},
        onImageListLoadedFail(error) {},
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
    }
};
