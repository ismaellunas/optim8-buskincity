import MixinContentHasImage from '@/Mixins/ContentHasImage';
import MixinMediaImage from '@/Mixins/MediaImage';
import { forEach, find } from 'lodash';
import { isBlank } from '@/Libs/utils';
import { oops as oopsAlert } from '@/Libs/alert';
import { emitter } from '@/Libs/utils';

export default {
    mixins: [
        MixinContentHasImage,
        MixinMediaImage,
    ],

    inject: {
        instructions: {
            default: () => {},
        }
    },

    setup() {
        return {
            pageMedia: [],
        };
    },

    data() {
        return {
            imageListQueryParams: {},
            imageListRouteName: 'admin.media.lists',
            modalImages: [],
        }
    },

    mounted() {
        const self = this;

        emitter.on('on-save-as-image', () => {
            self.refreshImageLists();
        });
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
                    self.modalImages = response.data;
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
        onImageListLoadedFail(error) {},

        search(term) {
            this.setTerm(term);
            this.refreshImageLists();
        },

        refreshImageLists() {
            this.getImagesList(route(this.imageListRouteName));
        },

        refreshImageListByPageActive() {
            let url = find(this.modalImages.links, 'active').url ?? null;

            if (url) {
                this.getImagesList(url);
            } else {
                this.refreshImageLists();
            }
        },
    },
};
