<template>
    <div class="mb-3">
        <div class="columns">
            <div class="column">
                <div class="field is-horizontal mb-5">
                    <biz-form-image-square
                        v-model="form.photo"
                        v-model:photo-url="imageUrl"
                        :label="i18n.profile_picture"
                        is-rounded-preview
                        :modal-title="i18n.profile_picture"
                        wrapper-class="field-body is-align-items-center"
                        :message="error('photo', errorBag)"
                        :original-image="photoUrl"
                        :show-delete-button="isDeleteButtonShown"
                        :notes="instructions?.profilePicture"
                        @on-cropped-image="onCroppedImage()"
                        @on-delete-image="onDeleteImage()"
                    >
                        <template #defaultImageView>
                            <biz-image
                                ratio="is-128x128"
                                rounded="is-rounded"
                                :src="userImage"
                            />
                        </template>
                    </biz-form-image-square>
                </div>
            </div>

            <div
                v-if="profilePageUrl && ! isFormDisabled"
                class="column"
            >
                <div class="buttons is-right">
                    <a
                        class="button as-text-black ml-1"
                        target="_blank"
                        title="Profile Page Url"
                        :href="profilePageUrl"
                    >
                        {{ i18n.open_public_profile }} &nbsp;
                        <i :class="icon.idCard" />
                    </a>
                </div>
            </div>
        </div>

        <biz-form-input
            v-model="form.first_name"
            :label="i18n.first_name"
            required
            :message="error('first_name', errorBag)"
        />

        <biz-form-input
            v-model="form.last_name"
            :label="i18n.last_name"
            required
            :message="error('last_name', errorBag)"
        />

        <biz-form-input
            v-model="form.email"
            :label="i18n.email"
            required
            type="email"
            :message="error('email', errorBag)"
        />

        <biz-form-select
            v-if="canSetRole"
            v-model="form.role"
            :label="i18n.role"
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

        <template v-if="! isFormDisabled">
            <biz-form-dropdown-search
                :label="i18n.language"
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
        </template>

        <template v-else>
            <biz-form-input
                v-model="selectedLanguage"
                :label="i18n.language"
                required
            />
        </template>
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch.vue';
    import BizFormImageSquare from '@/Biz/Form/ImageSquare.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizImage from '@/Biz/Image.vue';
    import icon from '@/Libs/icon-class';
    import { confirmDelete } from '@/Libs/alert';
    import { debounceTime, userImage } from '@/Libs/defaults';
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
            BizImage,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: {
            i18n: { default: () => ({
                profile_picture : 'Profile picture',
                Choose_a_picture : 'Choose a picture',
                first_name : 'First name',
                last_name : 'Last name',
                email : 'Email',
                role : 'Role',
                select_a_role : 'Select a role',
                language : 'Language',
                open_public_profile: 'Open public profile',
            }) },
            instructions: { default: () => {} },
            isFormDisabled: { default: false, },
        },

        props: {
            canSetRole: {type: Boolean, default: true},
            errorBag: {type: String, default: 'default'},
            modelValue: { type: Object, required: true },
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
                userImage: userImage,
                filteredLanguages: this.languageOptions.slice(0, 10),
                imageUrl: this.photoUrl,
                icon,
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
            onCroppedImage() {
                this.form.is_photo_deleted = false;
            },

            onDeleteImage() {
                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        this.imageUrl = null;

                        this.form.photo = null;
                        this.form.is_photo_deleted = true;
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
