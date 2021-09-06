<template>
    <div>
        <div class="columns">
            <div class="column is-full">
                <div class="content box">
                    <div class="field">
                        <div class="control">
                            <sdb-input-file
                                v-model="file"
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
                <div class="columns">
                    <div class="column">
                        <sdb-form-field-horizontal>
                            <template v-slot:label>
                                Search
                            </template>
                            <div class="columns">
                                <div class="column is-three-quarters">
                                    <sdb-input
                                        v-model="term"
                                        maxlength="255"
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
                    <div class="column is-one-fifth">
                        <p class="buttons is-pulled-right">
                            <sdb-button-icon
                                icon="fas fa-th"
                                title="Gallery View"
                                type="button"
                                :class="{'is-primary': view === 'gallery'}"
                                @click="setView('gallery')"
                            />
                            <sdb-button-icon
                                icon="fas fa-th-list"
                                title="List View"
                                type="button"
                                :class="{'is-primary': view === 'list'}"
                                @click="setView('list')"
                            />
                        </p>
                    </div>
                </div>
            </div>

            <component
                :is="isGalleryView ? 'SdbMediaGallery' : 'SdbMediaList'"
                :media="records.data"
            >
                <template v-slot:default="{ medium }">
                    <component
                        :is="isGalleryView ? 'SdbMediaGalleryItem' : 'SdbMediaListItem'"
                        :is-delete-enabled="isDeleteEnabled"
                        :is-download-enabled="isDownloadEnabled"
                        :is-edit-enabled="isEditEnabled"
                        :medium="medium"
                        @on-delete-clicked="deleteRecord"
                        @on-edit-clicked="openEditModal"
                        @on-preview-clicked="previewImage"
                    >

                        <template v-slot:actions="{medium}">
                            <slot name="actions" :media="medium"></slot>
                        </template>
                    </component>
                </template>
            </component>

            <div v-if="isPaginationDisplayed" class="column is-full">
                <sdb-pagination
                    :is-ajax="isAjax"
                    :links="records?.links ?? []"
                    :query-params="queryParams"
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
                            <sdb-image
                                :src="previewFileSrc"
                                :img-style="{maxHeight: 500+'px'}"
                            />
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
    import HasModalMixin from '@/Mixins/HasModal';
    import HasPageErrors from '@/Mixins/HasPageErrors';
    import MediaForm from '@/Pages/Media/Form';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbFormField from '@/Sdb/Form/Field';
    import SdbFormFieldHorizontal from '@/Sdb/Form/FieldHorizontal';
    import SdbImage from '@/Sdb/Image';
    import SdbInput from '@/Sdb/Input';
    import SdbInputFile from '@/Sdb/InputFile';
    import SdbMediaGallery from '@/Sdb/Media/Gallery';
    import SdbMediaGalleryItem from '@/Sdb/Media/GalleryItem';
    import SdbMediaList from '@/Sdb/Media/List';
    import SdbMediaListItem from '@/Sdb/Media/ListItem';
    import SdbModal from '@/Sdb/Modal';
    import SdbModalCard from '@/Sdb/ModalCard';
    import SdbModalImageEditor from '@/Sdb/Modal/ImageEditor';
    import SdbPagination from '@/Sdb/Pagination';
    import { acceptedFileTypes, acceptedImageTypes } from '@/Libs/defaults';
    import { confirm as confirmAlert, confirmDelete, success as successAlert } from '@/Libs/alert';
    import { getCanvasBlob } from '@/Libs/utils';
    import { includes } from 'lodash';
    import { ref } from "vue";
    import { useForm } from '@inertiajs/inertia-vue3';

    function getEmptyFormMedia() {
        return {
            file: null,
            file_url: null,
            file_name: null,
        };
    };

    export default {
        name: 'MediaLibrary',
        mixins: [
            HasPageErrors,
            HasModalMixin,
        ],
        components: {
            MediaForm,
            SdbButton,
            SdbButtonIcon,
            SdbFormField,
            SdbFormFieldHorizontal,
            SdbImage,
            SdbInput,
            SdbInputFile,
            SdbMediaGallery,
            SdbMediaGalleryItem,
            SdbMediaList,
            SdbMediaListItem,
            SdbModal,
            SdbModalCard,
            SdbModalImageEditor,
            SdbPagination,
        },
        emits: [
            'on-media-submitted',
            'on-view-changed'
        ],
        props: {
            acceptedTypes: {default: acceptedFileTypes},
            baseRouteName: {default: 'admin.media'},
            isAjax: {type: Boolean, default: false},
            isDeleteEnabled: {type: Boolean, default: true},
            isDownloadEnabled: {type: Boolean, default: true},
            isEditEnabled: {type: Boolean, default: true},
            isPaginationDisplayed: {type: Boolean, default: true},
            queryParams: { type: Object },
            records: {},
            search: Function,
        },
        setup(props) {
            return {
                view: ref(props.queryParams.view ?? 'gallery'),
                term: ref(props.queryParams.term),
            };
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
            },
            setView(view) {
                this.view = view;
                this.$emit('on-view-changed', view);
            },
        },
        computed: {
            previewFileSrc() {
                return this.formMedia?.file_url ?? this.formMedia?.file ?? '';
            },
            isProcessing() {
                return this.isUploading;
            },
            isGalleryView() {
                return this.view === 'gallery';
            },
        },
    }
</script>
