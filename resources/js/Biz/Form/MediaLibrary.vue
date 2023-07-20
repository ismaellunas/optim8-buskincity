<template>
    <biz-form-field
        :class="fieldClass"
        :is-required="required"
    >
        <template
            v-if="label"
            #label
        >
            {{ label }}
        </template>

        <div class="control">
            <div
                v-if="hasMediumPreview"
                class="columns"
            >
                <div
                    class="column"
                    :class="imagePreviewSizeClass"
                >
                    <biz-media-gallery-item
                        :medium="mediumPreview"
                        :is-edit-enabled="false"
                        :is-download-enabled="isDownloadEnabled"
                        :is-image-preview-thumbnail="isImagePreviewThumbnail"
                        @on-preview-clicked="onPreviewOpened"
                        @on-delete-clicked="onDeleted"
                    />
                </div>
            </div>

            <div class="buttons mb-0">
                <biz-button-icon
                    type="button"
                    :icon="icon.image"
                    :disabled="disabled"
                    @click="openModal"
                >
                    <span>
                        {{ placeholder }}
                    </span>
                </biz-button-icon>
            </div>

            <biz-modal
                v-show="isModalPreviewOpen"
                @close="onPreviewClosed()"
            >
                <p class="image">
                    <img
                        :src="previewImageSrc"
                        alt="preview-image"
                    >
                </p>
            </biz-modal>

            <biz-modal-media-browser
                v-if="isModalOpen"
                title="Media Library"
                :accepted-file-type="acceptedFileType"
                :data="media"
                :is-download-enabled="isDownloadEnabled"
                :is-upload-enabled="isUploadEnabled"
                :query-params="mediaListQueryParams"
                :search="search"
                :instructions="instructions"
                @close="closeModal"
                @on-clicked-pagination="getMediaList"
                @on-media-selected="onSelectMedia"
                @on-media-submitted="onUpdateMedia"
                @on-view-changed="setView"
            />
        </div>

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinMediaLibrary from '@/Mixins/MediaLibrary';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizFormField from '@/Biz/Form/Field.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizModal from '@/Biz/Modal.vue';
    import BizModalMediaBrowser from '@/Biz/Modal/MediaBrowser.vue';
    import BizMediaGalleryItem from '@/Biz/Media/GalleryItem.vue';
    import icon from '@/Libs/icon-class.js';
    import { useModelWrapper } from '@/Libs/utils';
    import { isEmpty } from 'lodash';
    import { confirmDelete } from '@/Libs/alert';
    import { acceptedFileGroups } from '@/Libs/defaults';

    export default {
        name: 'BizFormMediaLibrary',

        components: {
            BizButtonIcon,
            BizModal,
            BizModalMediaBrowser,
            BizFormField,
            BizInputError,
            BizMediaGalleryItem,
        },

        mixins: [
            MixinHasModal,
            MixinMediaLibrary,
        ],

        props: {
            disabled: { type: Boolean, default: false },
            fieldClass: { type: [Object, Array, String], default: undefined },
            instructions: {type: Array, default: () => []},
            isDownloadEnabled: { type: Boolean, default: true },
            isUploadEnabled: { type: Boolean, default: true },
            label: { type: String, default: null},
            mediaTypes: { type: Array, default: () => ['image'] },
            medium: { type: Object, default: () => {} },
            message: { type: [Array, Object, String], default: undefined },
            modelValue: { type: [String, Number, null], required: true },
            placeholder: { type: String, default: 'Open Media Library' },
            required: { type: Boolean, default: false },
            imagePreviewSize: {
                type: [String, Number],
                default: 3,
                validator(value) {
                    return (value >= 1 && value <= 12);
                }
            },
            isImagePreviewThumbnail: { type:Boolean, default: true },
        },

        emits: [
            'on-delete-clicked',
            'update:modelValue'
        ],

        setup(props, {emit}) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                actionClass: "card-footer-item p-2 is-borderless is-shadowless is-inverted",
                icon,
                previewImageSrc: null,
                isModalPreviewOpen: false,
                mediumPreview: this.medium,
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

            hasMediumPreview() {
                return !isEmpty(this.mediumPreview);
            },

            imagePreviewSizeClass() {
                return "is-" + this.imagePreviewSize;
            },
        },

        methods: {
            onPreviewOpened(medium) {
                this.isModalPreviewOpen = true;

                this.previewImageSrc = medium.file_url;
            },

            onPreviewClosed() {
                this.isModalPreviewOpen = false;

                this.previewImageSrc = null;
            },

            onDeleted() {
                const self = this;

                confirmDelete().then(result => {
                    if (result.isConfirmed) {
                        self.computedValue = null;
                        self.mediumPreview = {};
                    }
                })
            },

            onShownModal() { /* @override */
                this.setTerm('');

                this.getMediaList(route(this.mediaListRouteName));
            },

            onSelectMedia(file) {
                this.computedValue = file.id;
                this.mediumPreview = file;

                this.closeModal();
            },

            onUpdateMedia(response) {
                this.onSelectMedia(response.data[0]);

                this.closeModal();
            },
        },
    }
</script>