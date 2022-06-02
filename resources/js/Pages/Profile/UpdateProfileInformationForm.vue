<template>
    <form-section @submitted="updateProfileInformation">
        <template #title>
            Account Details
        </template>

        <template #form>
            <div class="field is-horizontal mb-5">
                <biz-form-image-square
                    v-model="form.photo"
                    v-model:photo-url="photoUrl"
                    label="Profile picture"
                    modal-title="Profile picture"
                    wrapper-class="field-body is-align-items-center"
                    :show-delete-button="isDeleteButtonShown"
                    :message="error('photo')"
                    @on-cropped-image="onCroppedImage()"
                    @on-delete-image="onDeleteImage()"
                    @on-reset-preview="resetPreview()"
                >
                    <template #default-image-view>
                        <biz-image
                            ratio="is-128x128"
                            rounded="is-rounded"
                            :src="defaultUserUrl"
                        />
                    </template>
                </biz-form-image-square>
            </div>

            <div class="field is-horizontal mb-5">
                <div class="field-body">
                    <biz-form-input
                        v-model="form.first_name"
                        disabled
                        label="First name"
                        required
                        :message="form.errors.first_name"
                    />
                    <biz-form-input
                        v-model="form.last_name"
                        disabled
                        label="Last name"
                        required
                        :message="form.errors.last_name"
                    />
                </div>
            </div>

            <div class="field mb-5">
                <biz-form-input
                    v-model="form.email"
                    disabled
                    label="Email"
                    required
                    type="email"
                    :message="form.errors.email"
                />
            </div>

            <div class="field mb-5">
                <biz-form-dropdown-search
                    label="Language"
                    required
                    :close-on-click="true"
                    :message="form.errors.language_id"
                    @search="searchLanguage($event)"
                >
                    <template #trigger>
                        <span :style="{'min-width': '4rem'}">
                            {{ selectedLanguage }}
                        </span>
                    </template>

                    <biz-dropdown-item
                        v-for="option in filteredLanguages"
                        :key="option.id"
                        @click="selectedLanguage = option"
                    >
                        {{ option.value }}
                    </biz-dropdown-item>
                </biz-form-dropdown-search>
            </div>
        </template>

        <template #actions>
            <div class="field mb-5">
                <biz-button
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    class="is-medium is-primary"
                >
                    <span class="has-text-weight-bold">Update details</span>
                </biz-button>
            </div>
        </template>
    </form-section>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch';
    import BizFormImageSquare from '@/Biz/Form/ImageSquare';
    import BizFormInput from '@/Biz/Form/Input';
    import BizImage from '@/Biz/Image';
    import FormSection from '@/Frontend/FormSection';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { acceptedImageTypes, debounceTime, defaultUserUrl } from '@/Libs/defaults';
    import { oops as oopsAlert, confirmDelete, success as successAlert } from '@/Libs/alert';
    import { find, debounce, isEmpty, filter } from 'lodash';

    export default {
        components: {
            BizButton,
            BizDropdownItem,
            BizFormDropdownSearch,
            BizFormImageSquare,
            BizFormInput,
            BizImage,
            FormSection,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        props: {
            languageOptions: { type: Array, default: () => [] },
            user: {
                type: Object,
                required: true,
            },
        },

        emits: [
            'after-update-profile'
        ],

        data() {
            return {
                cropper: null,
                acceptedTypes: acceptedImageTypes,
                form: this.$inertia.form({
                    _method: 'PUT',
                    first_name: this.user.first_name,
                    last_name: this.user.last_name,
                    email: this.user.email,
                    photo: null,
                    is_photo_deleted: false,
                    language_id: this.user.language_id
                }),
                photoUrl: this.user.profile_photo_url,
                isImageEditing: false,
                filteredLanguages: this.languageOptions.slice(0, 10),
                defaultUserUrl: defaultUserUrl,
            }
        },

        computed: {
            isDeleteButtonShown() {
                return (
                    !isEmpty(this.photoUrl)
                    && !isEmpty(this.user.profile_photo_url)
                );
            },

            selectedLanguage: {
                get() {
                    if (this.form.language_id) {
                        let language = find(
                            this.languageOptions,
                            ['id', parseInt(this.form.language_id)]
                        );

                        if (language) {
                            return language.value;
                        }
                    }
                    return '';
                },
                set(language) {
                    this.form.language_id = language.id;
                }
            },
        },

        methods: {
            updateProfileInformation() {
                this.onStartLoadingOverlay();

                this.form.post(route('user-profile-information.update'), {
                    errorBag: 'updateProfileInformation',
                    preserveScroll: true,
                    onSuccess: () => {
                        this.resetImageForm();

                        this.$emit('after-update-profile');

                        successAlert("Saved");
                    },
                    onError: () => {
                        oopsAlert();
                    },
                    onFinish: () => {
                        this.onEndLoadingOverlay();
                    },
                });
            },

            resetImageForm() {
                this.form.reset('photo', 'is_photo_deleted');
            },

            resetPreview() {
                this.photoUrl = this.user.profile_photo_url;
            },

            onDeleteImage() {
                const self = this;
                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.photoUrl = null;
                        self.form.photo = null;
                        self.form.is_photo_deleted = true;
                    }
                })
            },

            onCroppedImage() {
                this.form.is_photo_deleted = false;
            },

            searchLanguage: debounce(function(term) {
                if (!isEmpty(term) && term.length > 1) {
                    this.filteredLanguages = filter(this.languageOptions, function (language) {
                        return new RegExp(term, 'i').test(language.value);
                    }).slice(0, 10);
                } else {
                    this.filteredLanguages = this.languageOptions.slice(0, 10);
                }
            }, debounceTime),
        },
    }
</script>
