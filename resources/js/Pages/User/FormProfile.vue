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
            label="Language"
            required
            :close-on-click="true"
            :message="error('language_id')"
            @search="searchLanguage($event)"
        >
            <template #trigger>
                <span :style="{'min-width': '4rem'}">
                    {{ selectedDefaultLanguage }}
                </span>
            </template>

            <biz-dropdown-item
                v-for="option in filteredLanguages"
                :key="option.id"
                @click="selectedDefaultLanguage = option"
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
    import { usePage } from '@inertiajs/inertia-vue3';

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
        },

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
                languageOptions: usePage().props.value.shownLanguageOptions,
            };
        },

        data() {
            return {
                filteredLanguages: this.languageOptions.slice(0, 10),
            };
        },

        computed: {
            selectedDefaultLanguage: {
                get() {
                    if (this.form.language_id) {
                        let language = find(
                            this.languageOptions,
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
