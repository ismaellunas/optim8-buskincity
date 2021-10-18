<template>
    <jet-form-section @submitted="updateProfileInformation">
        <template #title>
            Profile Information
        </template>

        <template #description>
            Update your account's profile information and email address.
        </template>

        <template #form>
            <!-- Profile Photo -->
            <div class="col-span-6 sm:col-span-4" v-if="$page.props.jetstream.managesProfilePhotos">
                <!-- Profile Photo File Input -->
                <input type="file" class="hidden"
                            ref="photo"
                            @change="updatePhotoPreview">

                <sdb-label for="photo" value="Photo" />

                <!-- Current Profile Photo -->
                <div class="mt-2" v-show="! photoPreview">
                    <img :src="user.profile_photo_url" :alt="user.full_name" class="rounded-full h-20 w-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" v-show="photoPreview">
                    <span class="block rounded-full w-20 h-20"
                          :style="'background-size: cover; background-repeat: no-repeat; background-position: center center; background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                <sdb-button 
                    @click.prevent="selectNewPhoto"
                    type="button" 
                    class="is-warning mt-2 mr-2" 
                >
                    Select A New Photo
                </sdb-button>

                <sdb-button 
                    v-if="user.profile_photo_path"
                    @click.prevent="deletePhoto" 
                    type="button" 
                    class="is-warning mt-2" 
                >
                    Remove Photo
                </sdb-button>

                <jet-input-error :message="form.errors.photo" class="mt-2" />
            </div>

            <sdb-form-input 
                v-model="form.first_name"
                label="First Name"
                required
                :message="form.errors.first_name"
            ></sdb-form-input>

            <sdb-form-input 
                v-model="form.last_name"
                label="Last Name"
                required
                :message="form.errors.last_name"
            ></sdb-form-input>

            <sdb-form-input
                v-model="form.email"
                label="Email"
                required
                type="email"
                :message="form.errors.email"
            ></sdb-form-input>

        </template>

        <template #actions>
            <sdb-action-message :on="form.recentlySuccessful" class="mr-3">
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
    </jet-form-section>
</template>

<script>
    import JetFormSection from '@/Jetstream/FormSection';
    import JetInputError from '@/Jetstream/InputError';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbActionMessage from '@/Sdb/ActionMessage';
    import SdbButton from '@/Sdb/Button';
    import SdbLabel from '@/Sdb/Label';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';

    export default {
        components: {
            JetFormSection,
            JetInputError,
            SdbFormInput,
            SdbErrorNotifications,
            SdbActionMessage,
            SdbButton,
            SdbLabel,
        },
        mixins: [
            MixinHasPageErrors,
        ],

        props: ['user'],

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
