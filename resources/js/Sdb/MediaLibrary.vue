<template>
    <div>
        <div class="columns">
            <div class="column is-full">
                <div class="content box">
                    <div class="field">
                        <div class="control">
                            <sdb-input-file
                                v-model="file"
                                class="is-fullwidth"
                                :accept="acceptedTypes"
                                :is-name-displayed="false"
                                @on-file-picked="onFilePicked"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="columns is-multiline">

            <div class="column is-full">
                <sdb-form-field-horizontal>
                    <template v-slot:label>
                        Search
                    </template>
                    <div class="columns">
                        <div class="column is-three-quarters">
                            <sdb-input
                                v-model="term"
                                maxlength="255"
                                :disabled="isSearching"
                                @keyup.enter.prevent="search(term)"
                            />
                        </div>
                        <div class="column">
                            <sdb-button-icon
                                icon="fas fa-search"
                                type="button"
                                @click="search(term)"
                            />
                        </div>
                    </div>
                </sdb-form-field-horizontal>
            </div>

            <div
                v-for="media in records?.data"
                class="column"
                :class="itemClass"
            >
                <div class="card card-equal-height">
                    <div class="card-image px-2 pt-2 has-text-centered">
                        <figure v-if="isImage(media)">
                            <img
                                :src="media.thumbnail_url"
                                :alt="media.file_name_without_extension"
                            />
                        </figure>
                        <span
                            v-else
                            class="icon is-large"
                        >
                            <span class="fa-stack fa-lg">
                                <i :class="[mediaIconThumbnail(media), 'fa-5x']"></i>
                            </span>
                        </span>
                    </div>

                    <div class="card-content p-2">
                        <div
                            class="content"
                            style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis;"
                        >
                            <p>{{ media.file_name_without_extension }}</p>
                        </div>
                    </div>

                    <footer class="card-footer">

                        <sdb-button-icon
                            v-if="isImage(media)"
                            class="card-footer-item p-2 is-borderless is-shadowless is-info is-inverted"
                            icon="fas fa-expand"
                            title="Preview"
                            type="button"
                            @click="previewImage(media)"
                        />
                        <sdb-button-icon
                            v-if="isEditEnabled"
                            class="card-footer-item p-2 is-borderless is-shadowless is-primary is-inverted"
                            icon="fas fa-pen"
                            type="button"
                            @click="openEditModal(media)"
                        />
                        <sdb-button-icon
                            v-if="isDeleteEnabled"
                            class="card-footer-item p-2 is-borderless is-shadowless is-danger is-inverted"
                            icon="far fa-trash-alt"
                            title="Delete"
                            type="button"
                            @click="deleteRecord(media)"
                        />
                        <sdb-button-download
                            v-if="isDownloadEnabled"
                            class="card-footer-item p-2 is-borderless is-shadowless is-danger is-inverted"
                            title="Download"
                            :url="media.file_url"
                        />

                        <slot name="actions" :media="media"></slot>
                    </footer>
                </div>
            </div>

            <div v-if="isPaginationDisplayed" class="column is-full">
                <sdb-pagination
                    :is-ajax="isAjax"
                    :links="records?.links ?? []"
                />
            </div>
        </div>

        <sdb-modal
            v-show="isModalOpen"
            @close="closeModal()"
        >
            <p class="image">
                <img :src="previewImageSrc" alt="">
            </p>
        </sdb-modal>

        <sdb-modal-card
            v-if="isEditing"
            :content-class="{'is-huge': isImage(formMedia)}"
            :is-close-hidden="true"
            @close="closeEditModal"
        >
            <template v-slot:header>
                <p class="modal-card-title">Media Detail</p>
                <sdb-button
                    aria-label="close"
                    class="delete is-primary"
                    type="button"
                    @click="closeEditModal"
                />
            </template>

            <div class="columns">
                <div class="column">
                    <div
                        v-if="isImage(formMedia)"
                        class="card"
                    >
                        <div class="card-image">
                            <figure class="image">
                                <img :src="previewFileSrc" :style="{maxHeight: 500+'px'}"/>
                            </figure>
                        </div>
                        <footer class="card-footer">
                            <sdb-button
                                class="card-footer-item is-borderless is-shadowless"
                                type="button"
                                @click="openImageEditorModal"
                            >
                                Edit Image
                            </sdb-button>
                        </footer>
                    </div>
                    <div
                        v-else
                        class="card"
                        style="height: 90%"
                    >
                        <div class="card-image" style="height: inherit">
                            <span class="icon is-large" style="width: 100%">
                                <span class="fa-stack fa-lg">
                                    <i :class="[mediaIconThumbnail(formMedia), 'fa-6x']"></i>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <media-form
                        :media="formMedia"
                        :isAjax="isAjax"
                        @on-success-submit="onSuccessSubmit"
                        @cancel="closeEditModal"
                    />
                </div>
            </div>
        </sdb-modal-card>

        <sdb-modal-image-editor
            v-if="isImageEditing"
            v-model="formMedia.file_url"
            v-model:cropper="cropper"
            :file-name="formMedia.file_name"
            :is-processing="isProcessing"
            @close="closeImageEditorModal"
        >
            <template v-slot:actions="slotProps">
                <template v-if="formMedia.id">
                    <sdb-button
                        type="button"
                        :class="{'is-loading': isUploading, 'is-link': true}"
                        :disabled="isProcessing"
                        @click="updateImage"
                    >
                        Save
                    </sdb-button>
                    <sdb-button
                        type="button"
                        :class="{'is-loading': isUploading, 'is-primary': true}"
                        :disabled="isProcessing"
                        @click="saveAsImageConfirm"
                    >
                        Save As New
                    </sdb-button>
                    <sdb-button
                        type="button"
                        class="is-link is-light"
                        :disabled="isProcessing"
                        @click="closeImageEditorModal"
                    >
                        Cancel
                    </sdb-button>
                </template>
                <template v-else>
                    <sdb-button
                        type="button"
                        :class="{'is-loading': isUploading, 'is-link': true}"
                        :disabled="isProcessing"
                        @click="updateFile"
                    >
                        Done
                    </sdb-button>
                </template>
            </template>
        </sdb-modal-image-editor>
    </div>
</template>

<script>
    import HasPageErrors from '@/Mixins/HasPageErrors';
    import HasModalMixin from '@/Mixins/HasModal';
    import MediaForm from '@/Pages/Media/Form';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbButtonDownload from '@/Sdb/ButtonDownload';
    import SdbFormField from '@/Sdb/Form/Field';
    import SdbFormFieldHorizontal from '@/Sdb/Form/FieldHorizontal';
    import SdbInput from '@/Sdb/Input';
    import SdbInputFile from '@/Sdb/InputFile';
    import SdbModal from '@/Sdb/Modal';
    import SdbModalCard from '@/Sdb/ModalCard';
    import SdbModalImageEditor from '@/Sdb/Modal/ImageEditor';
    import SdbPagination from '@/Sdb/Pagination';
    import { acceptedFileTypes, acceptedImageTypes } from '@/Libs/defaults';
    import { confirm as confirmAlert, confirmDelete, success as successAlert } from '@/Libs/alert';
    import { getCanvasBlob } from '@/Libs/utils';
    import { includes, isEmpty } from 'lodash';
    import { reactive, ref } from "vue";
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    function getEmptyFormMedia() {
        return {
            file: null,
            file_url: null,
            file_name: null,
        };
    };

    export default {
        mixins: [
            HasPageErrors,
            HasModalMixin,
        ],
        components: {
            MediaForm,
            SdbButton,
            SdbButtonIcon,
            SdbButtonLink,
            SdbButtonDownload,
            SdbFormField,
            SdbFormFieldHorizontal,
            SdbInput,
            SdbInputFile,
            SdbModal,
            SdbModalCard,
            SdbModalImageEditor,
            SdbPagination,
        },
        emits: ['on-media-submitted'],
        props: {
            isAjax: {type: Boolean, default: false},
            isPaginationDisplayed: {type: Boolean, default: true},
            itemClass: {default: ['is-3']},
            records: {},
            baseRouteName: {default: 'admin.media'},
            acceptedTypes: {default: acceptedFileTypes},
            isEditEnabled: {type: Boolean, default: true},
            isDeleteEnabled: {type: Boolean, default: true},
            isDownloadEnabled: {type: Boolean, default: true},
            search: Function,
        },
        data() {
            return {
                cropper: null,
                file: null,
                formMedia: getEmptyFormMedia(),
                isEditing: false,
                isImageEditing: false,
                isUploading: false,
                previewImageSrc: null,
                messageText: {
                    successSaveAsMedia: "A new media has been created",
                    successSubmitForm: "Media has been updated",
                },
                term: '',
            };
        },
        methods: {
            isImage(media) {
                return (
                    (media?.is_image)
                    || (media?.file && media.file.type.startsWith("image"))
                );
            },
            previewImage(media) {
                this.previewImageSrc = media.file_url;
                this.openModal();
            },
            openEditModal(media) {
                this.isEditing = true;
                if (media) {
                    this.formMedia = media;
                    this.formMedia.file_name = media.file_name_without_extension;
                }
            },
            closeEditModal() {
                this.isEditing = false;
            },
            onFilePicked(event) {
                let fileName = this.file.name
                    .split('.').slice(0, -1).join('.')
                    .toLowerCase()
                    .replace(/[^a-z0-9]/gi, '-')
                    .replace(/-+/g, "-")

                this.formMedia = {
                    file: this.file,
                    file_name: fileName,
                    file_url: event.target.result,
                    is_image: includes(acceptedImageTypes, this.file.type),
                };
                this.openEditModal();
            },
            onSuccessSubmit(page) {
                this.file = null;
                this.formMedia = getEmptyFormMedia();
                this.closeEditModal();
                this.$emit('on-media-submitted', page);
            },
            deleteRecord(record) {
                confirmDelete('Are you sure?').then((result) => {
                    if (result.isConfirmed) {
                        this.$inertia.delete(route(this.baseRouteName+'.destroy', {id: record.id}));
                    }
                });
            },
            openImageEditorModal() {
                this.isImageEditing = true;
            },
            closeImageEditorModal() {
                this.isImageEditing = false;
            },
            /* @return Promise */
            getCropperBlob() {
                return getCanvasBlob(this.cropper.getCroppedCanvas());
            },
            updateFile() {
                const self = this;
                self.getCropperBlob()
                    .then((blob) => {
                        self.formMedia.file_url = URL.createObjectURL(blob);
                        self.formMedia.file = blob;
                        self.formMedia.is_image = true;
                        self.closeImageEditorModal();
                    });
            },
            updateImage() {
                const self = this;
                const media = this.formMedia;
                const url = route(this.baseRouteName+'.update-image', media.id);
                const cropper = this.cropper;

                self.isUploading = true;

                self.getCropperBlob().then((blob) => {
                    cropper.disable();

                    const form = useForm({
                        image: blob,
                        file_name: media.file_name,
                    });

                    form.post(url, {
                        preserveState: true,
                        preserveScroll: true,
                        onSuccess: (page) => {
                            const updatedMedia = page.props.records.data.find((record) => record.id === media.id);
                            self.formMedia.file_url = updatedMedia.file_url;
                            self.closeImageEditorModal();
                        },
                        onError: (errors) => {
                            if (self.$page.props.debug) {
                                console.log(error);
                            }
                        },
                        onFinish: () => {
                            if (cropper) {
                                cropper.enable();
                            }
                            self.isUploading = false;
                        },
                    });
                });
            },
            saveAsImageConfirm() {
                confirmAlert("Are you sure?", "You will create a new image")
                    .then((result) => result.isConfirmed ? this.saveAsImage() : false);
            },
            saveAsImage() {
                const self = this;
                const media = this.formMedia;
                const url = route(this.baseRouteName+'.save-as-media', media.id);

                self.isUploading = true;

                self.getCropperBlob().then((blob) => {

                    self.cropper.disable();

                    const form = useForm({
                        image: blob,
                        filename: media.file_name,
                    });

                    form.post(url, {
                        preserveState: true,
                        preserveScroll: true,
                        onSuccess: (page) => {
                            self.closeImageEditorModal();
                            self.closeEditModal();
                            successAlert(self.messageText.successSaveAsMedia);
                        },
                        onError: (errors) => {
                            if (self.$page.props.debug) {
                                console.log(error);
                            }
                        },
                        onFinish: () => {
                            if (self.cropper) {
                                self.cropper.enable();
                            }
                            self.isUploading = false;
                        },
                    });
                });
            },
            mediaIconThumbnail(media) {
                if (!media) return;

                if (media.file_type === "video") {
                    return "far fa-file-video";
                } else if (media.extension) {
                    if (media.extension === "pdf") {
                        return "far fa-file-pdf";
                    } else if (media.extension.startsWith('doc')) {
                        return "far fa-file-word";
                    } else if (media.extension.startsWith('ppt')) {
                        return "far fa-file-powerpoint";
                    } else if (media.extension.startsWith('xls')) {
                        return "far fa-file-excel";
                    }
                }
                return "far fa-file-alt";
            }
        },
        computed: {
            previewFileSrc() {
                return this.formMedia?.file_url ?? this.formMedia?.file ?? '';
            },
            isProcessing() {
                return this.isUploading;
            },
            isNew() {
                return !this.formMedia?.id;
            },
        },
    }
</script>
