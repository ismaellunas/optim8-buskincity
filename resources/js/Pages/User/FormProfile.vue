<template>
    <div class="mb-3">
        <div class="columns">
            <div class="column">
                <div class="field is-horizontal mb-5">
                    <biz-form-image-square
                        v-model="form.photo"
                        v-model:photo-url="imageUrl"
                        label="Profile picture"
                        modal-title="Profile picture"
                        :show-delete-button="isDeleteButtonShown"
                        :message="error('photo', errorBag)"
                        @on-cropped-image="onCroppedImage()"
                        @on-delete-image="onDeleteImage()"
                        @on-reset-preview="resetPreview()"
                    >
                        <template #default-image-view>
                            <user-icon style="width: 128px;" />
                        </template>
                    </biz-form-image-square>
                </div>
            </div>

            <div
                v-if="profilePageUrl"
                class="column"
            >
                <div class="buttons is-right">
                    <a
                        class="button as-text-black ml-1"
                        target="_blank"
                        title="Profile Page Url"
                        :href="profilePageUrl"
                    >
                        Open Public Profile &nbsp;
                        <i class="fas fa-id-card" />
                    </a>
                </div>
            </div>
        </div>

        <biz-form-input
            v-model="form.first_name"
            label="First Name"
            required
            :message="error('first_name', errorBag)"
        />

        <biz-form-input
            v-model="form.last_name"
            label="Last Name"
            required
            :message="error('last_name', errorBag)"
        />

        <biz-form-input
            v-model="form.email"
            label="Email"
            required
            type="email"
            :message="error('email', errorBag)"
        />

        <biz-form-select
            v-if="canSetRole"
            v-model="form.role"
            label="Role"
            placeholder="- Select a Role -"
            :message="error('role', errorBag)"
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
            label="Language"
            required
            :close-on-click="true"
            :message="error('language_id', errorBag)"
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
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch';
    import BizFormImageSquare from '@/Biz/Form/ImageSquare';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormSelect from '@/Biz/Form/Select';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import UserIcon from '@/Biz/Icon/User';
    import { confirmDelete } from '@/Libs/alert';
    import { debounceTime } from '@/Libs/defaults';
    import { find, debounce, isEmpty, filter } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'UserProfileForm',

        components: {
            BizDropdownItem,
            BizFormDropdownSearch,
            BizFormImageSquare,
            BizFormInput,
            BizFormSelect,
            UserIcon,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            canSetRole: {type: Boolean, default: true},
            errorBag: {type: String, default: 'default'},
            modelValue: {},
            photoUrl: {type: [String, null], default: null},
            profilePageUrl: {type: String, default: ''},
            roleOptions: {type: Array, default: () => []},
            languageOptions: {type: Array, default: () => []},
        },

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                filteredLanguages: this.languageOptions.slice(0, 10),
                imageUrl: this.photoUrl,
            };
        },

        computed: {
            isDeleteButtonShown() {
                return (
                    !isEmpty(this.imageUrl)
                    && !isEmpty(this.photoUrl)
                );
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
            resetPreview() {
                this.imageUrl = this.photoUrl;
            },

            onCroppedImage() {
                this.form.is_photo_deleted = false;
            },

            onDeleteImage() {
                const self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.form.photo = null;
                        self.form.photo_url = null;
                        self.form.is_photo_deleted = true;

                        self.imageUrl = null;
                    }
                })
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
    };
</script>
