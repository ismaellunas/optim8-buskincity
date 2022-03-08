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

                <biz-form-dropdown-search
                    label="Language"
                    :close-on-click="true"
                    :message="form.errors.language_id"
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
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormImageEditable from '@/Biz/Form/ImageEditable';
    import BizFormSection from '@/Biz/FormSection';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { find, debounce, isEmpty, filter } from 'lodash';
    import { confirmDelete } from '@/Libs/alert';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizActionMessage,
            BizButton,
            BizDropdownItem,
            BizFormDropdownSearch,
            BizFormInput,
            BizFormImageEditable,
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

        emits: [
            'after-update-profile'
        ],

        setup() {
            return {
                languageOptions: usePage().props.value.shownLanguageOptions,
            };
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
                    language_id: this.user.language_id
                }),
                isImageEditing: false,
                filteredLanguages: this.languageOptions.slice(0, 10),
            }
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
            updateProfileInformation() {
                this.form.post(route('user-profile-information.update'), {
                    errorBag: 'updateProfileInformation',
                    preserveScroll: true,
                    onSuccess: () => {
                        this.form.photo = null;
                        this.form.profile_photo_media_id = this.user.profile_photo_media_id;
                    },
                    onFinish: () => {
                        this.$emit('after-update-profile');
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

            searchLanguage: debounce(function(term) {
                if (!isEmpty(term) && term.length > 1) {
                    this.filteredLanguages = filter(this.languageOptions, function (language) {
                        return new RegExp(term, 'i').test(language.value);
                    }).slice(0, 10);
                } else {
                    this.filteredLanguages = this.languageOptions.slice(0, 10);
                }
            }, 750),
        },
    }
</script>
