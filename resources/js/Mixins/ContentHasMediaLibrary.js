import MixinContentHasImage from '@/Mixins/ContentHasImage';
import MixinMediaImage from '@/Mixins/MediaImage';
import { forEach } from 'lodash';
import { isBlank } from '@/Libs/utils';
import { oops as oopsAlert } from '@/Libs/alert';

export default {
    mixins: [
        MixinContentHasImage,
        MixinMediaImage,
    ],
    setup() {
        return {
            pageMedia: [],
        };
    },
    data() {
        return {
            imageListQueryParams: {},
            imageListRouteName: 'admin.media.lists',
        }
    },
    methods: {
        selectImage(image) {
            let hasImage = false;
            const locale = this.selectedLocale;

            if (!isBlank(this.entityImage.mediaId)) {
                this.detachImageFromMedia(this.entityImage.mediaId, this.pageMedia);
            }

            if (!this.images[ locale ]) {
                this.images[ locale ] = [];
            }

            forEach(this.images[ locale ], function(value) {
                if (value.id === image.id) {
                    value = image;
                    hasImage = true;
                }
            });

            if (!hasImage) {
                this.images[locale].push(image);
            }

            this.entityImage.mediaId = image.id;

            this.attachImageToMedia(image.id, this.pageMedia);

            this.onImageSelected();
        },
        updateImage(response) {
            this.selectImage(response.data[0]);
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
