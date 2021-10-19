import MixinGetImageContent from '@/Mixins/GetImageContent';
import { remove } from 'lodash';
import { isBlank } from '@/Libs/utils';
import { oops as oopsAlert } from '@/Libs/alert';

export default {
    mixins: [
        MixinGetImageContent,
    ],
    setup() {
        return {
            pageMedia: [],
        };
    },
    data() {
        return {
            imageListQueryParams: {},
            imageListRouteName: 'admin.media.list.image',
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
            axios.get(url, {params: this.imageListQueryParams})
                .then(function (response) {
                    self.onImageListLoadedSuccess(response.data)
                })
                .catch(function (error) {
                    oopsAlert();
                    self.onImageListLoadedFail(error);
                });
        },
        setTerm(term) {
            this.imageListQueryParams['term'] = term;
        },
        setView(view) {
            this.imageListQueryParams['view'] = view;
        },
        onImageSelected() {},
        onImageUpdated() {},
        onImageListLoadedSuccess(data) {},
        onImageListLoadedFail(error) {},
        search(term) {
            this.setTerm(term);
            this.getImagesList(route(this.imageListRouteName));
        },
    },
};
