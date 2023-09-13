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
                v-if="hasMediumsPreview"
                class="columns is-multiline"
            >
                <div
                    v-for="(medium, index) in mediumsPreview"
                    :key="index"
                    class="column"
                    :class="imagePreviewSizeClass"
                >
                    <biz-media-gallery-item
                        :medium="medium"
                        :is-delete-enabled="isBrowseEnabled"
                        :is-download-enabled="isDownloadEnabled"
                        :is-edit-enabled="isEditEnabled"
                        :is-select-enabled="false"
                        @on-preview-clicked="onPreviewOpened"
                        @on-delete-clicked="onDeleted"
                    />
                </div>
            </div>

            <div class="buttons mb-0">
                <biz-button-icon
                    type="button"
                    :icon="icon.image"
                    :disabled="isDisabled"
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
                :allow-multiple="allowMultiple"
                :data="media"
                :is-download-enabled="isDownloadEnabled"
                :is-edit-enabled="isEditEnabled"
                :is-upload-enabled="(isUploadEnabled && maxFileNumber > 0)"
                :query-params="mediaListQueryParams"
                :search="search"
                :max-files="maxFileNumber"
                :max-file-size="maxFileSize"
                :instructions="instructionMediaLibrary"
                :selected-media="selectedMedia"
                @close="onCloseModalMediaLibrary"
                @on-clicked-pagination="getMediaList"
                @on-media-submitted="onUpdateMedia"
                @on-view-changed="setView"
                @on-multiple-media-selected="onSelectMedia"
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
    import { confirmDelete } from '@/Libs/alert';
    import { acceptedFileGroups } from '@/Libs/defaults';
    import { reactive } from 'vue';
    import { cloneDeep } from 'lodash';

    export default {
        name: 'BizFormMultipleMediaLibrary',

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

        provide() {
            return {
                selectedMedia: this.selectedMedia,
                injectMediaDimension: this.dimension,
            }
        },

        props: {
            allowMultiple: { type: Boolean, default: false, },
            dimension: { type: Object, default: () => {} },
            disabled: { type: Boolean, default: false },
            fieldClass: { type: [Object, Array, String], default: undefined },
            instructions: {type: Array, default: () => []},
            isBrowseEnabled: { type: Boolean, default: true },
            isDownloadEnabled: { type: Boolean, default: true },
            isEditEnabled: { type: Boolean, default: true },
            isUploadEnabled: { type: Boolean, default: true },
            label: { type: String, default: null},
            maxFiles: { type: Number, default: 1, },
            maxFileSize: { type: [String, Number], default: null, },
            mediaTypes: { type: Array, default: () => ['image'] },
            mediums: { type: Array, default: () => [] },
            message: { type: [Array, Object, String], default: undefined },
            modelValue: { type: Array, required: true },
            placeholder: { type: String, default: 'Open Media Library' },
            required: { type: Boolean, default: false },
            imagePreviewSize: {
                type: [String, Number],
                default: 3,
                validator(value) {
                    return (value >= 1 && value <= 12);
                }
            },
        },

        emits: [
            'on-delete-clicked',
            'update:modelValue'
        ],

        setup(props, {emit}) {
            const selectedMedia = reactive({
                mediaIds: cloneDeep(props.modelValue),
                media: cloneDeep(props.mediums),
            });

            return {
                computedValue: useModelWrapper(props, emit),
                selectedMedia,
            };
        },

        data() {
            return {
                actionClass: "card-footer-item p-2 is-borderless is-shadowless is-inverted",
                icon,
                previewImageSrc: null,
                isModalPreviewOpen: false,
                mediumsPreview: this.mediums,
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

            hasMediumsPreview() {
                return this.mediumsPreview.length > 0;
            },

            imagePreviewSizeClass() {
                return "is-" + this.imagePreviewSize;
            },

            maxFileNumber() {
                return this.maxFiles - this.selectedMedia.mediaIds.length;
            },

            instructionMediaLibrary() {
                return [
                    ...this.instructions,
                    ...[
                        'Max file upload: ' + this.maxFileNumber
                    ]
                ];
            },

            isDisabled() {
                return (this.disabled || ! this.isBrowseEnabled);
            },
        },

        methods: {
            openModal() { /* @override */
                if (! this.isDisabled) {
                    this.isModalOpen = true;
                    this.onShownModal();
                }
            },

            onPreviewOpened(medium) {
                this.isModalPreviewOpen = true;

                this.previewImageSrc = medium.file_url;
            },

            onPreviewClosed() {
                this.isModalPreviewOpen = false;

                this.previewImageSrc = null;
            },

            onDeleted(medium) {
                const self = this;

                confirmDelete().then(result => {
                    if (result.isConfirmed) {
                        self.removeMedium(medium);
                        self.removeMediumFromPreview(medium);

                        self.setDefaultSelectedMedia();
                    }
                })
            },

            removeMedium(medium) {
                const indexToRemove = this.computedValue.findIndex(mediaId => mediaId === medium.id);

                if (indexToRemove !== -1) {
                    this.computedValue.splice(indexToRemove, 1);
                }
            },

            removeMediumFromPreview(medium) {
                const indexToRemove = this.mediumsPreview.findIndex(media => media.id === medium.id);

                if (indexToRemove !== -1) {
                    this.mediumsPreview.splice(indexToRemove, 1);
                }
            },

            onShownModal() { /* @override */
                this.setTerm('');

                this.getMediaList(route(this.mediaListRouteName));
            },

            onSelectMedia(files = []) {
                const self = this;

                if (files.length > 0) {
                    files.forEach(function (file) {
                        self.selectedMedia.mediaIds.push(file.id);
                        self.selectedMedia.media.push(file);
                    });
                }

                self.computedValue = cloneDeep(self.selectedMedia.mediaIds);
                self.mediumsPreview = cloneDeep(self.selectedMedia.media);

                self.closeModal();
            },

            onUpdateMedia(response) {
                this.onSelectMedia(response.data);
            },

            onCloseModalMediaLibrary() {
                this.setDefaultSelectedMedia();

                this.closeModal();
            },

            setDefaultSelectedMedia() {
                this.selectedMedia.mediaIds = cloneDeep(this.computedValue);
                this.selectedMedia.media = cloneDeep(this.mediumsPreview);
            },
        },
    }
</script>