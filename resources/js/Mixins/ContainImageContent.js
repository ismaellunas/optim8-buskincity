import { attachImageToMedia, detachImageFromMedia } from '@/Libs/page-builder';
import { isBlank } from '@/Libs/utils';

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
        selectImage(image) {
            if (!isBlank(this.image.mediaId)) {
                detachImageFromMedia(this.image.mediaId, this.pageMedia);
            }

            this.image.src = image.file_url;
            this.image.mediaId = image.id;

            attachImageToMedia(image.id, this.pageMedia);

            this.onImageSelected();
        },
        updateImage(response) {
            this.selectImage(response.data);
            this.onImageUpdated();
        },
        onImageSelected() {},
        onImageUpdated() {},
    },
};
