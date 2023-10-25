import MixinHasModal from '@/Mixins/HasModal';
import MixinMediaLibrary from '@/Mixins/MediaLibrary';
import { acceptedFileGroups } from '@/Libs/defaults';
import { confirm as confirmAlert } from '@/Libs/alert';
import { find } from 'lodash';

export default {
    mixins: [
        MixinHasModal,
        MixinMediaLibrary,
    ],

    inject: {
        i18n: { default: () => {} },
    },

    props: {
        disabled: { type: Boolean, default: false },
        isBrowseEnabled: { type: Boolean, default: true },
        isEditEnabled: { type: Boolean, default: true },
        mediaTypes: { type: Array, default: () => ['image'] },
        imagePreviewSize: {
            type: [String, Number],
            default: 3,
            validator(value) {
                return (value >= 1 && value <= 12);
            }
        },
    },

    data() {
        return {
            isModalEdit: false,
            isModalPreviewOpen: false,
            previewImageSrc: null,
            selectedEditedMedia: {},
        };
    },

    computed: {
        acceptedFileType() {
            let fileTypes = [];

            this.mediaTypes.forEach(function (type) {
                fileTypes = [
                    ...fileTypes,
                    ...acceptedFileGroups[type] ?? []
                ];
            });

            return fileTypes;
        },

        imagePreviewSizeClass() {
            return "is-" + this.imagePreviewSize;
        },

        isDisabled() {
            return (this.disabled || ! this.isBrowseEnabled);
        },
    },

    methods: {
        openModal() { /* @override /Mixins/HasModal */
            if (! this.isDisabled) {
                this.isModalOpen = true;
                this.onShownModal();
            }
        },

        onShownModal() { /* @override /Mixins/HasModal */
            this.setTerm('');

            this.refreshMediaList();
        },

        onPreviewOpened(medium) {
            this.isModalPreviewOpen = true;

            this.previewImageSrc = medium.file_url;
        },

        onPreviewClosed() {
            this.isModalPreviewOpen = false;

            this.previewImageSrc = null;
        },

        onEditedExistingMedia(media) {
            if (this.isEditEnabled) {
                if (media.can_edit_existing_media) {
                    if (
                        media.is_in_use
                        || media.is_in_use_multiple
                    ) {
                        confirmAlert(
                            this.i18n.edit_resource,
                            this.i18n.warning_edit_resource,
                            this.i18n.yes,
                            {
                                icon: 'warning'
                            }
                        ).then((result) => {
                            if (result.isConfirmed) {
                                this.selectedEditedMedia = media;

                                this.isModalEdit = true;
                            }
                        });
                    } else {
                        this.selectedEditedMedia = media;

                        this.isModalEdit = true;
                    }
                } else {
                    this.openModal();
                }
            }
        },

        closeEditModal() {
            this.isModalEdit = false;
        },

        refreshMediaListByPageActive() {
            let url = find(this.media.links, 'active').url ?? null;

            if (url) {
                this.getMediaList(url);
            } else {
                this.refreshMediaList();
            }
        }
    },
}