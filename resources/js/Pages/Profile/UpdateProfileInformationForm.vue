<template>
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
                v-if="$page.props.jetstream.managesProfilePhotos"
                class="col-span-6 sm:col-span-4"
            >
                <!-- Profile Photo File Input -->
                <input
                    ref="photo"
                    type="file"
                    class="hidden"
                    @change="updatePhotoPreview"
                >

                <sdb-label
                    for="photo"
                    value="Photo"
                />

                <!-- Current Profile Photo -->
                <div
                    v-show="! photoPreview"
                    class="mt-2"
                >
                    <img
                        :src="user.profile_photo_url"
                        :alt="user.full_name"
                        class="rounded-full h-20 w-20 object-cover"
                    >
                </div>

                <!-- New Profile Photo Preview -->
                <div
                    v-show="photoPreview"
                    class="mt-2"
                >
                    <span
                        class="block rounded-full w-20 h-20"
                        :style="'background-size: cover; background-repeat: no-repeat; background-position: center center; background-image: url(\'' + photoPreview + '\');'"
                    />
                </div>

                <sdb-button
                    class="is-warning mt-2 mr-2"
                    type="button"
                    @click.prevent="selectNewPhoto"
                >
                    Select A New Photo
                </sdb-button>

                <sdb-button
                    v-if="user.profile_photo_path"
                    class="is-warning mt-2"
                    type="button"
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
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbActionMessage from '@/Sdb/ActionMessage';
    import SdbButton from '@/Sdb/Button';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormSection from '@/Sdb/FormSection';
    import SdbInputError from '@/Sdb/InputError';
    import SdbLabel from '@/Sdb/Label';

    export default {
        components: {
            SdbActionMessage,
            SdbButton,
            SdbFormInput,
            SdbFormSection,
            SdbInputError,
            SdbLabel,
        },
        mixins: [
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
                form: this.$inertia.form({
                    _method: 'PUT',
                    first_name: this.user.first_name,
                    last_name: this.user.last_name,
                    email: this.user.email,
                    photo: null,
                }),

                photoPreview: null,
            }
        },

        methods: {
            updateProfileInformation() {
                if (this.$refs.photo) {
                    this.form.photo = this.$refs.photo.files[0]
                }

                this.form.post(route('user-profile-information.update'), {
                    errorBag: 'updateProfileInformation',
                    preserveScroll: true,
                    onSuccess: () => (this.clearPhotoFileInput()),
                });
            },

            selectNewPhoto() {
                this.$refs.photo.click();
            },

            updatePhotoPreview() {
                const photo = this.$refs.photo.files[0];

                if (! photo) return;

                const reader = new FileReader();

                reader.onload = (e) => {
                    this.photoPreview = e.target.result;
                };

                reader.readAsDataURL(photo);
            },

            deletePhoto() {
                this.$inertia.delete(route('current-user-photo.destroy'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.photoPreview = null;
                        this.clearPhotoFileInput();
                    },
                });
            },

            clearPhotoFileInput() {
                if (this.$refs.photo?.value) {
                    this.$refs.photo.value = null;
                }
            },
        },
    }
</script>
