import { isBlank } from '@/Libs/utils';
import { oops as oopsAlert } from '@/Libs/alert';
import { remove } from 'lodash';

export default {
    setup() {
        return {
            pageMedia: [],
        };
    },
    data() {
        return {
            image: {
                mediaId: null,
                src: null,
            },
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
            if (!isBlank(this.image.mediaId)) {
                this.detachImageFromMedia(this.image.mediaId, this.pageMedia);
            }

            this.image.src = image.file_url;
            this.image.mediaId = image.id;

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
