import { acceptedImageTypes, acceptedVideoTypes } from '@/Libs/defaults';

export default {
    name: 'MixinMediaTextEditor',

    data() {
        return {
            fileType: null,
        };
    },

    computed: {
        acceptedTypes() {
            switch (this.fileType) {
            case "media":
                return acceptedVideoTypes;
                break;

            default:
                return acceptedImageTypes;
                break;
            }
        },
    },

    methods: {
        setTypeToMedia(fileType) {
            this.fileType = fileType;

            switch (this.fileType) {
            case "media":
                this.setType(['video']);
                break;

            default:
                this.setType([fileType]);
                break;
            }
        },
    },
}