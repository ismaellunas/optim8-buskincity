<template>
    <div>
        <biz-form-section @submitted="updateProfileInformation">
            <template #title>
                Account
            </template>

            <template #description>
                Update your account's profile information and email address.
            </template>

            <template #form>
                <biz-form-input-image
                    v-model="form.photo"
                    v-model:photo-url="form.photo_url"
                    delete-label="Remove Photo"
                    modal-label="Profile Photo"
                    :message="error('photo')"
                    :photo-url="form.photo_url"
                    :show-delete-button="form.photo_url != null"
                    @on-reset-value="resetImageForm()"
                    @on-delete-image="onDeleteImage()"
                />

                <biz-form-input
                    v-model="form.first_name"
                    label="First Name"
                    required
                    :message="form.errors.first_name"
                />

                <biz-form-input
                    v-model="form.last_name"
                    label="Last Name"
                    required
                    :message="form.errors.last_name"
                />

                <biz-form-input
                    v-model="form.email"
                    label="Email"
                    required
                    type="email"
                    :message="form.errors.email"
                />
            </template>

            <template #actions>
                <biz-action-message
                    :is-active="form.recentlySuccessful"
                    class="mr-3"
                >
                    Saved.
                </biz-action-message>

                <biz-button
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    class="is-primary"
                >
                    Save
                </biz-button>
            </template>
        </biz-form-section>
    </div>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizActionMessage from '@/Biz/ActionMessage';
    import BizButton from '@/Biz/Button';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormInputImage from '@/Biz/Form/InputImage';
    import BizFormSection from '@/Biz/FormSection';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { confirmDelete } from '@/Libs/alert';

    export default {
        components: {
            BizActionMessage,
            BizButton,
            BizFormInput,
            BizFormInputImage,
            BizFormSection,
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
                    photo_url: this.user.profile_photo_url,
                    profile_photo_media_id: this.user.profile_photo_media_id,
                }),
                isImageEditing: false,
            }
        },

        methods: {
            updateProfileInformation() {
                this.form.post(route('user-profile-information.update'), {
                    errorBag: 'updateProfileInformation',
                    preserveScroll: true,
                    onSuccess: () => {
                        this.form.photo = null;
                        this.form.profile_photo_media_id = this.user.profile_photo_media_id;
                    },
                });
            },

            resetImageForm() {
                this.form.reset('photo', 'photo_url');
            },

            onDeleteImage() {
                const self = this;
                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.form.photo = null;
                        self.form.photo_url = null;
                        self.form.profile_photo_media_id = null;
                    }
                })
            },
        },
    }
</script>
