<template>
    <div>
        <div
            v-if="isUploadEnabled"
            class="columns"
        >
            <div class="column is-full">
                <div class="content box">
                    <div class="field">
                        <div class="control">
                            <biz-input-file
                                v-model="file"
                                :accept="acceptedTypes"
                                :is-name-displayed="false"
                                :disabled="isProcessing"
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
                        <biz-filter-search
                            v-model="term"
                            @search="search"
                        ></biz-filter-search>
                    </div>

                    <div
                        v-if="isFilterEnabled"
                        class="column"
                    >
                        <biz-dropdown
                            :close-on-click="false"
                        >
                            <template v-slot:trigger>
                                <span>Filter</span>
                                <span
                                    v-if="types.length > 0"
                                    class="ml-1"
                                >
                                    ({{types.length}})
                                </span>
                                <span class="icon is-small">
                                    <i class="fas fa-angle-down" aria-hidden="true"></i>
                                </span>
                            </template>

                            <biz-dropdown-item
                                v-for="(type, typeIndex) in typeOptions"
                                >
                                <biz-checkbox
                                    v-model:checked="types"
                                    :value="typeIndex"
                                    @change="$emit('on-type-changed', types)"
                                >
                                    &nbsp;{{type}}
                                </biz-checkbox>
                            </biz-dropdown-item>
                        </biz-dropdown>
                    </div>

                    <div class="column is-one-fifth">
                        <biz-buttons-display-view
                            v-model="view"
                            class="buttons is-pulled-right"
                            @on-view-changed="$emit('on-view-changed', $event)"
                        />
                    </div>
                </div>
            </div>

            <component
                :is="isGalleryView ? 'BizMediaGallery' : 'BizMediaList'"
                :media="records.data"
            >
                <template v-slot:default="{ medium }">
                    <component
                        :is="isGalleryView ? 'BizMediaGalleryItem' : 'BizMediaListItem'"
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
                <biz-pagination
                    :is-ajax="isAjax"
                    :links="records?.links ?? []"
                    :query-params="queryParams"
                />
            </div>
        </div>

        <biz-modal
            v-show="isModalOpen"
            @close="closeModal()"
        >
            <p class="image">
                <img :src="previewImageSrc" alt="">
            </p>
        </biz-modal>

        <biz-modal-card
            v-if="isEditing"
            :content-class="{'is-huge': isImage(formMedia)}"
            :is-close-hidden="true"
            @close="closeEditModal"
        >
            <template v-slot:header>
                <p class="modal-card-title">Media Detail</p>
                <biz-button
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
                            <biz-image
                                :src="previewFileSrc"
                                :img-style="{maxHeight: 500+'px'}"
                            />
                        </div>
                        <footer class="card-footer">
                            <biz-button
                                class="card-footer-item is-borderless is-shadowless"
                                type="button"
                                @click="openImageEditorModal"
                            >
                                Edit Image
                            </biz-button>
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
                        @on-error-submit="onErrorSubmit"
                        @cancel="closeEditModal"
                    />
                </div>
            </div>
        </biz-modal-card>

        <biz-modal-image-editor
            v-if="isImageEditing"
            v-model="formMedia.file_url"
            v-model:cropper="cropper"
            :file-name="formMedia.file_name"
            :is-processing="isProcessing"
            @close="closeImageEditorModal"
        >
            <template v-slot:actions="slotProps">
                <template v-if="formMedia.id">
                    <biz-button
                        type="button"
                        :class="{'is-loading': isUploading, 'is-link': true}"
                        :disabled="isProcessing"
                        @click="updateImage"
                    >
                        Save
                    </biz-button>
                    <biz-button
                        type="button"
                        :class="{'is-loading': isUploading, 'is-primary': true}"
                        :disabled="isProcessing"
                        @click="saveAsImageConfirm"
                    >
                        Save As New
                    </biz-button>
                    <biz-button
                        type="button"
                        class="is-link is-light"
                        :disabled="isProcessing"
                        @click="closeImageEditorModal"
                    >
                        Cancel
                    </biz-button>
                </template>
                <template v-else>
                    <biz-button
                        type="button"
                        :class="{'is-loading': isUploading, 'is-link': true}"
                        :disabled="isProcessing"
                        @click="updateFile"
                    >
                        Done
                    </biz-button>
                </template>
            </template>
        </biz-modal-image-editor>
    </div>
</template>

<script>
    import HasModalMixin from '@/Mixins/HasModal';
    import HasPageErrors from '@/Mixins/HasPageErrors';
    import MediaForm from '@/Pages/Media/Form';
    import BizButton from '@/Biz/Button';
    import BizButtonsDisplayView from '@/Biz/ButtonsDisplayView';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizFormField from '@/Biz/Form/Field';
    import BizImage from '@/Biz/Image';
    import BizInputFile from '@/Biz/InputFile';
    import BizMediaGallery from '@/Biz/Media/Gallery';
    import BizMediaGalleryItem from '@/Biz/Media/GalleryItem';
    import BizMediaList from '@/Biz/Media/List';
    import BizMediaListItem from '@/Biz/Media/ListItem';
    import BizModal from '@/Biz/Modal';
    import BizModalCard from '@/Biz/ModalCard';
    import BizModalImageEditor from '@/Biz/Modal/ImageEditor';
    import BizPagination from '@/Biz/Pagination';
    import { acceptedFileTypes, acceptedImageTypes } from '@/Libs/defaults';
    import { confirm as confirmAlert, confirmDelete, success as successAlert, oops as oopsAlert } from '@/Libs/alert';
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
        components: {
            MediaForm,
            BizButton,
            BizButtonsDisplayView,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizFilterSearch,
            BizFormField,
            BizImage,
            BizInputFile,
            BizMediaGallery,
            BizMediaGalleryItem,
            BizMediaList,
            BizMediaListItem,
            BizModal,
            BizModalCard,
            BizModalImageEditor,
            BizPagination,
        },
        mixins: [
            HasPageErrors,
            HasModalMixin,
        ],
        emits: [
            'on-media-submitted',
            'on-view-changed'
        ],
        props: {
            acceptedTypes: {type: Array, default: acceptedFileTypes},
            baseRouteName: {default: 'admin.media'},
            isAjax: {type: Boolean, default: false},
            isDeleteEnabled: {type: Boolean, default: true},
            isDownloadEnabled: {type: Boolean, default: true},
            isEditEnabled: {type: Boolean, default: true},
            isFilterEnabled: {type: Boolean, default: false},
            isPaginationDisplayed: {type: Boolean, default: true},
            isUploadEnabled: {type: Boolean, default: true},
            queryParams: { type: Object },
            records: {},
            search: Function,
            typeOptions: {type: Object, default: {
                'image': "Image",
                'video': "Video",
                'document': "Document",
                'spreadsheet': "Spreadsheet",
                'presentation': "Presentation",
                'presentation': "Presentation",
            }},
        },
        setup(props) {
            return {
                view: ref(props.queryParams.view ?? 'gallery'),
                term: ref(props.queryParams.term),
                types: ref(props.queryParams?.types ?? []),
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
                isDeleting: false,
                loader: null,
                previewImageSrc: null,
                disabled: false,
                messageText: {
                    successSaveAsMedia: "A new media has been created",
                    successSubmitForm: "Media has been updated",
                    successDeleteMedia: "Deleted",
                },
            };
        },
        computed: {
            previewFileSrc() {
                return this.formMedia?.file_url ?? this.formMedia?.file ?? '';
            },
            isProcessing() {
                return this.isUploading || this.isDeleting;
            },
            isGalleryView() {
                return this.view === 'gallery';
            },
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
                let fileType = "." + this.file.type
                    .split('/')[1];

                this.formMedia = {
                    file: this.file,
                    file_name: fileName,
                    file_url: event.target.result,
                    is_image: includes(acceptedImageTypes, fileType),
                };
                this.openEditModal();
            },
            onSuccessSubmit(page) {
                this.file = null;
                this.formMedia = getEmptyFormMedia();
                this.closeEditModal();
                this.$emit('on-media-submitted', page);
            },
            onErrorSubmit() {
                oopsAlert();
            },
            deleteRecord(record) {
                confirmDelete('Are you sure?').then((result) => {
                    const self = this;
                    let loader = null;

                    if (result.isConfirmed) {
                        this.$inertia.delete(
                            route(this.baseRouteName+'.destroy', {id: record.id}),
                            {
                                onStart: visit => {
                                    loader = self.$loading.show();
                                    self.isDeleting = true;
                                },
                                onSuccess: page => {
                                    successAlert(self.messageText.successDeleteMedia);
                                },
                                onError: errors => {
                                    oopsAlert();
                                },
                                onFinish: visit => {
                                    loader.hide();
                                    self.isDeleting = false;
                                },
                            }
                        );
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
                        onStart: () => self.onStartLoadingOverlay(),
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
                            self.onEndLoadingOverlay();
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
                const url = route(this.baseRouteName+'.save-as-image', media.id);

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
                        onStart: () => self.onStartLoadingOverlay(),
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
                            self.onEndLoadingOverlay();
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
            onStartLoadingOverlay() {
                this.loader = this.$loading.show();
            },
            onEndLoadingOverlay() {
                this.loader.hide();
            },
        },
    }
</script>
