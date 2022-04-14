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
                <biz-form-image-editable
                    v-model="form.photo"
                    v-model:photo-url="photoUrl"
                    modal-label="Profile Photo"
                    delete-label="Remove Photo"
                    :photo-url="photoUrl"
                    :show-delete-button="hasPhoto"
                    :message="error('photo')"
                    @on-reset-value="resetImageForm()"
                    @on-delete-image="onDeleteImage()"
                >
                    <template #default-image-view>
                        <user-icon
                            style="width: 64px;"
                        />
                    </template>
                </biz-form-image-editable>

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

                <biz-form-dropdown-search
                    label="Country"
                    required
                    :close-on-click="true"
                    :message="form.errors.country_code"
                    @search="searchCountry($event)"
                >
                    <template #trigger>
                        <span :style="{'min-width': '4rem'}">
                            {{ selectedCountry }}
                        </span>
                    </template>

                    <biz-dropdown-item
                        v-for="option in filteredCountries"
                        :key="option.id"
                        @click="selectedCountry = option"
                    >
                        {{ option.value }}
                    </biz-dropdown-item>
                </biz-form-dropdown-search>

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
            </template>

            <template #actions>
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
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormImageEditable from '@/Biz/Form/ImageEditable';
    import BizFormSection from '@/Biz/FormSection';
    import UserIcon from '@/Biz/Icon/User';
    import { acceptedImageTypes, debounceTime } from '@/Libs/defaults';
    import { oops as oopsAlert, confirmDelete, success as successAlert } from '@/Libs/alert';
    import { find, debounce, isEmpty, filter } from 'lodash';

    export default {
        components: {
            BizButton,
            BizDropdownItem,
            BizFormDropdownSearch,
            BizFormInput,
            BizFormImageEditable,
            BizFormSection,
            UserIcon,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
            MixinHasPageErrors,
        ],

        props: {
            countryOptions: { type: Array, default: () => [] },
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
                    country_code: this.user.country_code,
                    photo: null,
                    is_photo_deleted: false,
                    language_id: this.user.language_id
                }),
                photoUrl: this.user.profile_photo_url,
                isImageEditing: false,
                filteredCountries: this.countryOptions.slice(0, 10),
                filteredLanguages: this.languageOptions.slice(0, 10),
            }
        },

        computed: {
            hasPhoto() {
                return this.photoUrl != null && this.photoUrl != '';
            },

            selectedCountry: {
                get() {
                    if (this.form.country_code) {
                        let country = find(
                            this.countryOptions,
                            ['id', this.form.country_code]
                        );
                        return country.value;
                    }
                    return '';
                },
                set(country) {
                    this.form.country_code = country.id;
                }
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
                        this.form.photo = null;
                        this.form.is_photo_deleted = false;

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

            searchCountry: debounce(function(term) {
                if (!isEmpty(term) && term.length > 1) {
                    this.filteredCountries = filter(this.countryOptions, function (country) {
                        return new RegExp(term, 'i').test(country.value);
                    }).slice(0, 10);
                } else {
                    this.filteredCountries = this.countryOptions.slice(0, 10);
                }
            }, 750),

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
