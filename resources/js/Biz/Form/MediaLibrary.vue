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
                        :is-download-enabled="isDownloadEnabled"
                        :is-edit-enabled="isEditEnabled"
                        :is-image-preview-thumbnail="isImagePreviewThumbnail"
                        :is-delete-enabled="isBrowseEnabled"
                        @on-preview-clicked="onPreviewOpened"
                        @on-delete-clicked="onDeleted"
                        @on-edit-clicked="onEditedExistingMedia"
                    />
                </div>
            </div>

            <div class="buttons mb-0">
                <biz-button-icon
                    type="button"
                    :icon="imageIcon"
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
                :data="media"
                :is-download-enabled="isDownloadEnabled"
                :is-edit-enabled="isEditEnabled"
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

            <biz-modal-media-library-detail
                v-if="isModalEdit"
                :media="selectedEditedMedia"
                @update-media="updateMediaPreview"
                @close="closeEditModal()"
            />
        </div>

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import MixinFormMediaLibrary from '@/Mixins/FormMediaLibrary';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizFormField from '@/Biz/Form/Field.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizMediaGalleryItem from '@/Biz/Media/GalleryItem.vue';
    import BizModal from '@/Biz/Modal.vue';
    import BizModalMediaBrowser from '@/Biz/Modal/MediaBrowser.vue';
    import BizModalMediaLibraryDetail from '@/Biz/Modal/MediaLibraryDetail.vue';
    import { image as imageIcon } from '@/Libs/icon-class.js';
    import { useModelWrapper } from '@/Libs/utils';
    import { isEmpty, cloneDeep, isArray } from 'lodash';
    import { confirmDelete } from '@/Libs/alert';

    export default {
        name: 'BizFormMediaLibrary',

        components: {
            BizButtonIcon,
            BizFormField,
            BizInputError,
            BizMediaGalleryItem,
            BizModal,
            BizModalMediaBrowser,
            BizModalMediaLibraryDetail,
        },

        mixins: [
            MixinFormMediaLibrary,
        ],

        inject: ['i18n'],

        provide() {
            return {
                injectMediaDimension: this.dimension,
            }
        },

        props: {
            dimension: { type: Object, default: () => {} },
            fieldClass: { type: [Object, Array, String], default: undefined },
            instructions: {type: Array, default: () => []},
            isDownloadEnabled: { type: Boolean, default: true },
            isImagePreviewThumbnail: { type:Boolean, default: true },
            isUploadEnabled: { type: Boolean, default: true },
            label: { type: String, default: null},
            medium: { type: Object, default: () => {} },
            message: { type: [Array, Object, String], default: undefined },
            modelValue: { type: [String, Number, null], required: true },
            placeholder: { type: String, default: 'Open Media Library' },
            required: { type: Boolean, default: false },
        },

        setup(props, {emit}) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                imageIcon,
                mediumPreview: this.medium,
            };
        },

        computed: {
            hasMediumPreview() {
                return !isEmpty(this.mediumPreview);
            },
        },

        methods: {
            onDeleted() {
                const self = this;

                confirmDelete().then(result => {
                    if (result.isConfirmed) {
                        self.computedValue = null;
                        self.mediumPreview = {};
                    }
                })
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

            updateMediaPreview(media) {
                let cloneMedia = cloneDeep(media);

                cloneMedia.thumbnail_url = null;

                if (! isArray(cloneMedia.translations)) {
                    cloneMedia.translations = this.mediumPreview.translations;
                }

                this.mediumPreview = cloneMedia;
            },
        },
    }
</script>