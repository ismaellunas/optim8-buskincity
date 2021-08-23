<template>
<app-layout>
    <template #header>
        Media
    </template>

    <div class="box">
        <sdb-media-library
            :records="records"
            :upload-route="route('admin.media.upload-image')"
            @on-media-upload-success="onMediaUploadSuccess"
        >
            <template v-slot:actions="slotProps">
                <sdb-button
                    class="card-footer-item p-2 is-borderless is-shadowless is-primary is-inverted"
                    @click="openEditMedia(slotProps.media)"
                >
                    <span class="icon is-small">
                        <i class="fas fa-pen"></i>
                    </span>
                </sdb-button>
                <sdb-button
                    class="card-footer-item p-2 is-borderless is-shadowless is-danger is-inverted"
                    title="Delete"
                    @click="deleteRecord(slotProps.media)"
                >
                    <span class="icon is-small">
                        <i class="far fa-trash-alt"></i>
                    </span>
                </sdb-button>
            </template>
        </sdb-media-library>
    </div>

    <sdb-modal-card
        v-if="isEditing"
        :content-class="['is-huge']"
        :is-close-hidden="true"
        @close="closeEditMedia"
    >
        <template v-slot:header>
            <p class="modal-card-title">Media Detail</p>
            <sdb-button
                @click="closeEditMedia"
                class="delete is-primary"
                type="button"
                aria-label="close"
            />
        </template>
        <div class="columns">
            <div class="column">
                <div class="card">
                    <div class="card-image">
                        <figure class="image">
                            <img :src="selectedMedia.file_url" :style="{maxHeight: 500+'px'}">
                        </figure>
                    </div>
                    <footer class="card-footer">
                        <sdb-button
                            class="card-footer-item is-borderless is-shadowless"
                            @click="isImageEditing = true"
                        >
                            Edit Image
                        </sdb-button>
                    </footer>
                </div>
            </div>
            <div class="column">
                <media-form
                    :media="selectedMedia"
                    :localeOptions="localeOptions"
                    @on-success-submit="onSuccessSubmit"
                    @cancel="closeEditMedia"
                />
            </div>
        </div>
    </sdb-modal-card>

    <sdb-modal-image-editor
        v-if="isImageEditing"
        v-model="selectedMedia.file_url"
        v-model:cropper="cropper"
        :file-name="selectedMedia.file_name"
        :is-processing="isUploading"
        :update-image="updateImage"
        @close="closeImageEditorModal"
    >
        <template v-slot:actions="slotProps">
            <sdb-button
                @click="updateImage"
                :class="{'is-loading': isUploading, 'is-link': true}"
                :disabled="!canUpload"
            >
                Save
            </sdb-button>
            <sdb-button
                @click="saveAsImageConfirm"
                :class="{'is-loading': isUploading, 'is-primary': true}"
                :disabled="!canUpload"
            >
                Save As New
            </sdb-button>
            <sdb-button
                class="is-link is-light"
                :disabled="isUploading"
                @click="isImageEditing = false"
            >
                Cancel
            </sdb-button>
        </template>
    </sdb-modal-image-editor>
</app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import MediaForm from '@/Pages/Media/Form';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbMediaLibrary from '@/Sdb/MediaLibrary';
    import SdbModalCard from '@/Sdb/ModalCard';
    import SdbModalImageEditor from '@/Sdb/Modal/ImageEditor';
    import { success as successAlert, confirm as confirmAlert } from '@/Libs/alert';
    import { isBlank } from '@/Libs/utils';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            MediaForm,
            SdbButton,
            SdbButtonLink,
            SdbMediaLibrary,
            SdbModalCard,
            SdbModalImageEditor,
        },
        props: {
            records: {},
            baseRouteName: {},
            files: {default: []},
            localeOptions: Array,
        },
        data() {
            return {
                cropper: null,
                isEditing: false,
                isImageEditing: false,
                isUploading: false,
                selectedMedia: null,
                messageText: {
                    successSaveAsMedia: "A new media has been created",
                    successSubmitForm: "Media has been updated",
                },
            };
        },
        methods: {
            getEditRoute(id) {
                return route(this.baseRouteName+'.edit', {id});
            },
            deleteRecord(record) {
                if (!confirm('Are you sure?')) return;
                this.$inertia.delete(route(this.baseRouteName+'.destroy', {id: record.id}));
            },
            onMediaUploadSuccess(response) {
                this.$inertia.reload();
                successAlert('File has been uploaded');
            },
            openEditMedia(media) {
                this.selectedMedia = media;
                this.isEditing = true;
            },
            closeEditMedia(media) {
                this.isEditing = false;
            },
            closeImageEditorModal() {
                this.isImageEditing = false;
            },
            onSuccessSubmit() {
                this.closeEditMedia();
                successAlert(this.messageText.successSubmitForm);
            },
            updateImage() {
                const self = this;
                const media = this.selectedMedia;
                const url = route('admin.media.update-image', media.id);
                const cropper = this.cropper;
                const canvas = cropper.getCroppedCanvas();

                self.isUploading = true;

                canvas.toBlob((blob) => {

                    cropper.disable();

                    const form = useForm({
                        image: blob,
                        filename: media.file_name,
                    });

                    form.post(url, {
                        preserveState: true,
                        preserveScroll: true,
                        onSuccess: (page) => {
                            const updatedMedia = page.props.records.data.find((record) => record.id === media.id);
                            self.selectedMedia.file_url = updatedMedia.file_url;
                            self.closeImageEditorModal();
                        },
                        onError: (errors) => {
                            console.log(error);
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
                const media = this.selectedMedia;
                const url = route('admin.media.save-as-media', media.id);
                const cropper = this.cropper;
                const canvas = cropper.getCroppedCanvas();

                self.isUploading = true;

                canvas.toBlob((blob) => {

                    cropper.disable();

                    const form = useForm({
                        image: blob,
                        filename: media.file_name,
                    });

                    form.post(url, {
                        preserveState: true,
                        preserveScroll: true,
                        onSuccess: (page) => {
                            self.closeImageEditorModal();
                            self.closeEditMedia();
                            successAlert(self.messageText.successSaveAsMedia);
                        },
                        onError: (errors) => {
                            console.log(error);
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
        },
        computed: {
            canUpload() {
                return !this.isUploading;
            }
        },
    }
</script>
