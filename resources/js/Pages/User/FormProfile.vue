<template>
    <div class="mb-3">
        <biz-form-image-editable
            v-model="form.photo"
            v-model:photo-url="form.photo_url"
            modal-label="Profile Photo"
            delete-label="Remove Photo"
            :photo-url="form.photo_url"
            :show-delete-button="form.photo_url != null"
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
            :message="error('first_name')"
        />

        <biz-form-input
            v-model="form.last_name"
            label="Last Name"
            required
            :message="error('last_name')"
        />

        <biz-form-input
            v-model="form.email"
            label="Email"
            required
            type="email"
            :message="error('email')"
        />

        <biz-form-select
            v-if="canSetRole"
            v-model="form.role"
            label="Role"
            placeholder="- Select a Role -"
            :message="error('role')"
        >
            <option
                v-for="option in roleOptions"
                :key="option.id"
                :value="option.id"
            >
                {{ option.value }}
            </option>
        </biz-form-select>

        <biz-form-dropdown-search
            label="Country"
            :close-on-click="true"
            :message="error('country_code')"
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
            :message="error('language_id')"
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

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormImageEditable from '@/Biz/Form/ImageEditable';
    import BizFormSelect from '@/Biz/Form/Select';
    import UserIcon from '@/Biz/Icon/User';
    import { useModelWrapper } from '@/Libs/utils';
    import { debounceTime } from '@/Libs/defaults';
    import { confirmDelete } from '@/Libs/alert';
    import { find, debounce, isEmpty, filter } from 'lodash';

    export default {
        name: 'UserProfileForm',

        components: {
            BizDropdownItem,
            BizFormDropdownSearch,
            BizFormInput,
            BizFormImageEditable,
            BizFormSelect,
            UserIcon,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            canSetRole: {type: Boolean, default: true},
            modelValue: {},
            photoUrl: {type: [String, null], default: null},
            roleOptions: {type: Array, default: () => []},
            countryOptions: {type: Array, default: () => []},
            shownLanguageOptions: {type: Array, default: () => []},
        },

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                filteredCountries: this.countryOptions.slice(0, 10),
                filteredLanguages: this.shownLanguageOptions.slice(0, 10),
            };
        },

        computed: {
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
                            this.shownLanguageOptions,
                            ['id', parseInt(this.form.language_id)]
                        );
                        return language.value;
                    }
                    return '';
                },
                set(language) {
                    this.form.language_id = language.id;
                }
            },
        },

        methods: {
            resetImageForm() {
                this.form.reset('photo', 'photo_url', 'profile_photo_media_id');
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

            searchCountry: debounce(function(term) {
                if (!isEmpty(term) && term.length > 1) {
                    this.filteredCountries = filter(this.countryOptions, function (country) {
                        return new RegExp(term, 'i').test(country.value);
                    }).slice(0, 10);
                } else {
                    this.filteredCountries = this.countryOptions.slice(0, 10);
                }
            }, debounceTime),

            searchLanguage: debounce(function(term) {
                if (!isEmpty(term) && term.length > 1) {
                    this.filteredLanguages = filter(this.shownLanguageOptions, function (language) {
                        return new RegExp(term, 'i').test(language.value);
                    }).slice(0, 10);
                } else {
                    this.filteredLanguages = this.shownLanguageOptions.slice(0, 10);
                }
            }, debounceTime),
        },
    };
</script>
