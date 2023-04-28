<template>
    <div>
        <biz-error-notifications
            :bags="['userUpdate']"
            :errors="$page.props.errors"
        />

        <div class="mb-6">
            <form
                class="columns"
                method="post"
                @submit.prevent="onSubmit"
            >
                <div class="column">
                    <fieldset
                        class="box"
                        :disabled="isFormDisabled"
                    >
                        <h3 class="title is-3">
                            {{ i18n.profile }}
                        </h3>
                        <hr>

                        <form-user-profile
                            v-model="profileForm"
                            :can-set-role="!record.isSuperAdministrator"
                            :photo-url="photoUrl"
                            :role-options="roleOptions"
                            :language-options="supportedLanguageOptions"
                            :error-bag="errorBag"
                            :profile-page-url="can.public_profile ? record.profilePageUrl : null"
                        />

                        <div
                            v-if="! record.isTrashed"
                            class="field is-grouped is-grouped-right"
                        >
                            <div class="control">
                                <biz-button-link
                                    :href="route(baseRouteName+'.index')"
                                    class="is-link is-light"
                                >
                                    {{ i18n.cancel }}
                                </biz-button-link>
                            </div>
                            <div class="control">
                                <biz-button class="is-link">
                                    {{ i18n.update }}
                                </biz-button>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </form>

            <form
                v-if="showPasswordForm"
                class="columns"
                method="post"
                @submit.prevent="onPasswordSubmit"
            >
                <div class="column">
                    <fieldset
                        class="box"
                        :disabled="isFormDisabled"
                    >
                        <h3 class="title is-3">
                            {{ i18n.password }}
                        </h3>
                        <hr>

                        <form-user-password
                            v-model="passwordForm"
                            :error-bag="errorBag"
                        />

                        <div
                            v-if="! record.isTrashed"
                            class="field is-grouped is-grouped-right"
                        >
                            <div class="control">
                                <biz-button class="is-link">
                                    {{ i18n.update }}
                                </biz-button>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </form>

            <div
                v-show="isFormBuilderShown"
                class="columns"
            >
                <div class="column">
                    <fieldset
                        class="box"
                        :disabled="isFormDisabled"
                    >
                        <h3 class="title is-3">
                            {{ capitalCase(i18n.profile_information) }}
                        </h3>
                        <hr>

                        <form-builder
                            :key="biodataFormKey"
                            route-name="admin.users.edit"
                            :entity-id="record.id"
                            :locale="$page.props.user.origin_language_code"
                            :hide-buttons="isFormDisabled"
                            @loaded-empty-field="isFormBuilderShown = false"
                            @loaded-successfully="isFormBuilderShown = true"
                        />
                    </fieldset>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import FormBuilder from '@/Form/Builder.vue';
    import FormUserPassword from '@/Pages/User/FormPassword.vue';
    import FormUserProfile from '@/Pages/User/FormProfile.vue';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';
    import { capitalCase } from 'change-case';

    export default {
        name: 'UserEdit',

        components: {
            BizButton,
            BizButtonLink,
            BizErrorNotifications,
            FormBuilder,
            FormUserPassword,
            FormUserProfile,
        },

        provide() {
            return {
                isFormDisabled: this.isFormDisabled,
            };
        },

        layout: AppLayout,

        props: {
            can: { type: Object, required: true },
            errors: { type: Object, default: () => {} },
            record: {type: Object, default: () => {} },
            roleOptions: { type: Array, default: () => [] },
            supportedLanguageOptions: { type: Array, default: () => [] },
            title: { type: String, required: true },
            i18n: { type: Object, default: () => ({
                cancel : 'Cancel',
                update : 'Update',
                profile : 'Profile',
                profile_information : 'Profile information',
                password : 'Password',
            }) },
        },

        setup(props) {
            const userProfileForm = {
                _method: 'put',
                first_name: props.record.first_name,
                last_name: props.record.last_name,
                email: props.record.email,
                photo: null,
                is_photo_deleted: false,
                language_id: props.record.language_id,
            };

            if (!props.record.isSuperAdministrator) {
                userProfileForm['role'] = (props.record.roles[0])
                    ? props.record.roles[0].id
                    : null;
            }

            return {
                profileForm: useForm(userProfileForm),
                passwordForm: useForm({
                    password: null,
                    password_confirmation: null,
                }),
            };
        },

        data() {
            return {
                baseRouteName: 'admin.users',
                biodataFormKey: 0,
                errorBag: 'userUpdate',
                photoUrl: this.record.profile_photo_url,
                isFormBuilderShown: false,
                isProcessing: false,
                loader: null,
            };
        },

        computed: {
            isFormDisabled() {
                return this.isProcessing
                    || this.record.isTrashed
            },

            showPasswordForm() {
                return this.can.update_password
                    && ! this.isFormDisabled;
            },
        },

        methods: {
            onSubmit() {
                const self = this;
                self.profileForm.post(route(self.baseRouteName+'.update', self.record.id), {
                    preserveScroll: false,
                    onStart: () => {
                        self.loader = self.$loading.show();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        self.profileForm.isDirty = false;
                        self.profileForm.photo = null;
                        self.profileForm.is_photo_deleted = false;
                        self.photoUrl = self.record.profile_photo_url;

                        self.biodataFormKey += 1;
                    },
                    onFinish: () => {
                        self.loader.hide();
                        self.isProcessing = false;
                    }
                });
            },

            onPasswordSubmit() {
                const self = this;
                const form = self.passwordForm;

                form.put(route(self.baseRouteName+'.password', self.record.id), {
                    preserveScroll: false,
                    onStart: () => {
                        self.loader = self.$loading.show();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        form.isDirty = false;
                        form.reset();
                    },
                    onFinish: () => {
                        self.loader.hide();
                        self.isProcessing = false;
                    }
                });
            },

            capitalCase,
        },
    };
</script>
