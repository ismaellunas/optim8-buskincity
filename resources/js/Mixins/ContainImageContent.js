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
                    })
                }
            }
        },
        selectImage(image) {
            const locale = this.selectedLocale;
            const entityImage = this.entityImage;
            const pageImages = this.images;

            if (!isBlank(entityImage.mediaId)) {
                this.detachImageFromMedia(entityImage.mediaId, this.pageMedia);
            }

            if (!pageImages[ locale ]) {
                pageImages[ locale ] = []
            }
            pageImages[locale].push(image);

            entityImage.mediaId = image.id;

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
    },
};
