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
                    :class="imagePreviewSize"
                >
                    <div class="card card-equal-height">
                        <div class="card-image px-2 pt-2 has-text-centered">
                            <biz-image
                                v-if="isImage"
                                :alt="mediumPreview.display_file_name"
                                :src="mediumPreview.thumbnail_url ?? mediumPreview.file_url"
                            />
                            <span
                                v-else
                                class="icon is-large"
                            >
                                <span class="fa-stack fa-lg">
                                    <i :class="[thumbnailIcon, 'fa-5x']" />
                                </span>
                            </span>
                        </div>

                        <div class="card-content p-2">
                            <div
                                class="content"
                                style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis;"
                            >
                                <p>{{ mediumPreview.display_file_name }}</p>
                            </div>
                        </div>

                        <footer class="card-footer">
                            <biz-button-icon
                                v-if="isImage"
                                :icon="icon.expand"
                                title="Preview"
                                type="button"
                                :class="[actionClass, 'is-info']"
                                @click="onPreviewOpened(mediumPreview)"
                            />
                            <biz-button-icon
                                :icon="icon.remove"
                                title="Delete"
                                type="button"
                                :class="[actionClass, 'is-danger']"
                                @click="onDeleted"
                            />
                            <biz-button-download
                                v-if="isDownloadEnabled"
                                title="Download"
                                type="button"
                                :class="[actionClass, 'is-link']"
                                :url="mediumPreview.file_url"
                            />
                        </footer>
                    </div>
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
                        Open Media Library
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
                @close="closeModal"
                @on-clicked-pagination="getMediaList"
                @on-media-selected="onSelectMedia"
                @on-media-submitted="onUpdateMedia"
                @on-view-changed="setView"
            />
        </div>

        <slot name="note" />

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinMediaLibrary from '@/Mixins/MediaLibrary';
    import BizButtonDownload from '@/Biz/ButtonDownload.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizFormField from '@/Biz/Form/Field.vue';
    import BizImage from '@/Biz/Image.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizModal from '@/Biz/Modal.vue';
    import BizModalMediaBrowser from '@/Biz/Modal/MediaBrowser.vue';
    import icon from '@/Libs/icon-class.js';
    import { useModelWrapper } from '@/Libs/utils';
    import { isEmpty } from 'lodash';
    import { confirmDelete } from '@/Libs/alert';
    import { acceptedFileGroups } from '@/Libs/defaults';

    export default {
        name: 'BizFormMediaLibrary',

        components: {
            BizButtonIcon,
            BizButtonDownload,
            BizImage,
            BizModal,
            BizModalMediaBrowser,
            BizFormField,
            BizInputError,
        },

        mixins: [
            MixinHasModal,
            MixinMediaLibrary,
        ],

        props: {
            disabled: { type: Boolean, default: false },
            fieldClass: { type: [Object, Array, String], default: undefined },
            isDownloadEnabled: { type: Boolean, default: true },
            isUploadEnabled: { type: Boolean, default: true },
            label: { type: String, default: null},
            mediaTypes: { type: Array, default: () => ['image'] },
            medium: { type: Object, default: () => {} },
            message: { type: [Array, Object, String], default: undefined },
            modelValue: { type: [String, Number, null], required: true },
            required: { type: Boolean, default: false },
            imagePreviewSize: { type: [String, Object], default: 'is-3' },
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

            isImage() {
                return (
                    (this.mediumPreview?.isImage)
                    || (this.mediumPreview?.file_type && this.mediumPreview.file_type.startsWith("image"))
                );
            },

            thumbnailIcon() {
                if (this.mediumPreview?.file_type === "video") {
                    return this.icon.fileVideo;
                } else if (this.mediumPreview?.extension) {
                    if (this.mediumPreview?.extension === "pdf") {
                        return this.icon.filePdf;
                    } else if (this.mediumPreview?.extension.startsWith('doc')) {
                        return this.icon.fileWord;
                    } else if (this.mediumPreview?.extension.startsWith('ppt')) {
                        return this.icon.filePowerpoint;
                    } else if (this.mediumPreview?.extension.startsWith('xls')) {
                        return this.icon.fileExcel;
                    }
                }
                return this.icon.file;
            }
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
                this.onSelectMedia(response.data);

                this.closeModal();
            },
        },
    }
</script>