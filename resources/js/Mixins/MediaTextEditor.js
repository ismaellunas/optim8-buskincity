import { acceptedImageTypes, acceptedVideoTypes } from '@/Libs/defaults';

export default {
    name: 'MixinMediaTextEditor',

    inject: {
        instructions: {
            default: () => {},
        }
    },

    data() {
        return {
            fileType: null,
            mediaLibraryInstructions: [],
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

        setInstructions(fileType) {
            switch (fileType) {
                case "media":
                    this.mediaLibraryInstructions = this.instructions?.videoMediaLibrary;
                    break;

                default:
                    this.mediaLibraryInstructions = this.instructions?.mediaLibrary;
                    break;
            }
        },
    },
}