<template>
    <div>
        <sdb-form-section @submitted="updateProfileInformation">
            <template #title>
                Profile Information
            </template>

            <template #description>
                Update your account's profile information and email address.
            </template>

            <template #form>
                <!-- Profile Photo -->
                <div
                    class="col-span-6 sm:col-span-4 mb-3"
                >
                    <!-- Profile Photo File Input -->
                    <sdb-label
                        for="photo"
                        value="Photo"
                    />

                    <!-- Current Profile Photo -->
                    <div
                        class="mt-2 mb-2"
                    >
                        <sdb-image
                            rounded="is-rounded"
                            style="width: 64px;"
                            :src="photoUrl"
                        />
                    </div>

                    <sdb-input-file
                        v-model="file"
                        :accept="acceptedTypes"
                        :is-name-displayed="true"
                        :disabled="form.processing"
                        @on-file-picked="onFilePicked"
                    />

                    <sdb-button
                        v-if="user.profile_photo_url"
                        class="is-warning mt-2"
                        type="button"
                        :disabled="form.processing"
                        @click.prevent="deletePhoto"
                    >
                        Remove Photo
                    </sdb-button>

                    <sdb-input-error :message="form.errors.photo" />
                </div>

                <sdb-form-input
                    v-model="form.first_name"
                    label="First Name"
                    required
                    :message="form.errors.first_name"
                />

                <sdb-form-input
                    v-model="form.last_name"
                    label="Last Name"
                    required
                    :message="form.errors.last_name"
                />

                <sdb-form-input
                    v-model="form.email"
                    label="Email"
                    required
                    type="email"
                    :message="form.errors.email"
                />
            </template>

            <template #actions>
                <sdb-action-message
                    :is-active="form.recentlySuccessful"
                    class="mr-3"
                >
                    Saved.
                </sdb-action-message>

                <sdb-button
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    class="is-primary"
                >
                    Save
                </sdb-button>
            </template>
        </sdb-form-section>

        <!-- Image modal -->
        <sdb-modal-card
            v-if="isModalOpen"
            :is-close-hidden="true"
            @close="closeModal()"
        >
            <template #header>
                <p class="modal-card-title">
                    Profile Photo
                </p>
                <sdb-button
                    aria-label="close"
                    class="delete is-primary"
                    type="button"
                    @click="clearPhotoInput()"
                />
            </template>

            <div class="columns">
                <div class="column">
                    <div
                        class="card"
                    >
                        <div class="card-image">
                            <sdb-image
                                :src="form.photo_url"
                                :img-style="{maxHeight: 500+'px'}"
                            />
                        </div>
                        <footer class="card-footer">
                            <sdb-button
                                class="card-footer-item is-borderless is-shadowless"
                                type="button"
                                @click="isImageEditing = true"
                            >
                                Edit Image
                            </sdb-button>
                        </footer>
                    </div>
                </div>
            </div>

            <template #footer>
                <div
                    class="columns"
                    style="width: 100%"
                >
                    <div class="column">
                        <div class="is-pulled-right">
                            <div class="buttons">
                                <sdb-button
                                    class="is-danger"
                                    type="button"
                                    @click="clearPhotoInput()"
                                >
                                    Cancel
                                </sdb-button>
                                <sdb-button
                                    class="is-primary"
                                    type="button"
                                    @click="closeModal()"
                                >
                                    Save
                                </sdb-button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </sdb-modal-card>

        <sdb-modal-image-editor
            v-if="isImageEditing"
            v-model="form.photo_url"
            v-model:cropper="cropper"
            :is-processing="form.processing"
            @close="closeImageEditorModal"
        >
            <template #actions>
                <sdb-button
                    type="button"
                    class="is-link"
                    :disabled="form.processing"
                    @click="updateImageFile"
                >
                    Done
                </sdb-button>
            </template>
        </sdb-modal-image-editor>
        <!-- Image modal -->
    </div>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbModalCard from '@/Sdb/ModalCard';
    import SdbModalImageEditor from '@/Sdb/Modal/ImageEditor';
    import SdbActionMessage from '@/Sdb/ActionMessage';
    import SdbButton from '@/Sdb/Button';
    import SdbImage from '@/Sdb/Image';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormSection from '@/Sdb/FormSection';
    import SdbInputError from '@/Sdb/InputError';
    import SdbInputFile from '@/Sdb/InputFile';
    import SdbLabel from '@/Sdb/Label';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { includes } from 'lodash';
    import { getCanvasBlob } from '@/Libs/utils';
    import { oops as oopsAlert, confirmDelete } from '@/Libs/alert';

    export default {
        components: {
            SdbActionMessage,
            SdbButton,
            SdbImage,
            SdbModalCard,
            SdbModalImageEditor,
            SdbFormInput,
            SdbFormSection,
            SdbInputError,
            SdbInputFile,
            SdbLabel,
        },
        mixins: [
            MixinHasModal,
            MixinHasPageErrors,
        ],

        props: {
            user: {
                type: Object,
                required: true,
            },
        },

        data() {
            return {
                cropper: null,
                acceptedTypes: acceptedImageTypes,
                file: null,
                form: this.$inertia.form({
                    _method: 'PUT',
                    first_name: this.user.first_name,
                    last_name: this.user.last_name,
                    email: this.user.email,
                    photo: null,
                    photo_url: null,
                }),
                isImageEditing: false,
            }
        },

        computed: {
            photoUrl() {
                let url = null;

                if (this.form.photo_url) {
                    url = this.form.photo_url;
                } else if (this.user.profile_photo_url) {
                    url = this.user.profile_photo_url;
                } else {
                    url = "https://dummyimage.com/64x64/e5e5e5/000.png&text=TO";
                }
                return url;
            },
        },

        methods: {
            onFilePicked(event) {
                let fileType = "." + this.file.type.split('/')[1];

                if (includes(this.acceptedTypes, fileType)) {
                    this.form.photo = this.file;
                    this.form.photo_url = event.target.result;
                    this.openModal();
                } else {
                    this.file = null;

                    oopsAlert({
                        text: "File must be a image format!"
                    });
                }
            },

            getCropperBlob() {
                return getCanvasBlob(this.cropper.getCroppedCanvas());
            },

            closeImageEditorModal() {
                this.isImageEditing = false;
            },

            updateImageFile() {
                const self = this;
                self.getCropperBlob()
                    .then((blob) => {
                        self.form.photo = blob;
                        self.form.photo_url = URL.createObjectURL(blob);
                        self.closeImageEditorModal();
                    });
            },

            clearPhotoInput() {
                this.file = null;
                this.form.reset('photo', 'photo_url');

                this.closeModal();
            },

            updateProfileInformation() {
                this.form.post(route('user-profile-information.update'), {
                    errorBag: 'updateProfileInformation',
                    preserveScroll: true,
                    onSuccess: () => {
                        this.clearPhotoInput();
                    },
                });
            },

            deletePhoto() {
                const self = this;
                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.form.processing = true;
                        self.$inertia.delete(route('current-user-photo.destroy'), {
                            preserveScroll: true,
                            onSuccess: () => {
                                self.clearPhotoInput();
                            },
                            onFinish: () => {
                                self.form.processing = false;
                            },
                        });
                    }
                })
            },
        },
    }
</script>
