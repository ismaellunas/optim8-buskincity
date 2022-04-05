<template>
    <app-layout>
        <template #header>{{ title }}</template>

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
                        :disabled="isProcessing"
                    >
                        <h3 class="title is-3">Profile</h3>
                        <hr>

                        <form-user-profile
                            v-model="profileForm"
                            :can-set-role="!record.isSuperAdministrator"
                            :role-options="roleOptions"
                            :language-options="languageOptions"
                            :country-options="countryOptions"
                            :error-bag="errorBag"
                            :profile-page-url="can.public_profile ? record.profilePageUrl : null"
                        />

                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <biz-button-link
                                    :href="route(baseRouteName+'.index')"
                                    class="is-link is-light">
                                    Cancel
                                </biz-button-link>
                            </div>
                            <div class="control">
                                <biz-button class="is-link">
                                    Update
                                </biz-button>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </form>

            <form
                class="columns"
                method="post"
                @submit.prevent="onPasswordSubmit"
            >
                <div class="column">
                    <fieldset
                        class="box"
                        :disabled="isProcessing"
                    >
                        <h3 class="title is-3">Password</h3>
                        <hr>

                        <form-user-password
                            v-model="passwordForm"
                            :error-bag="errorBag"
                        />

                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <biz-button class="is-link">
                                    Update
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
                        :disabled="isProcessing"
                    >
                        <h3 class="title is-3">
                            Profile
                        </h3>
                        <hr>

                        <form-builder
                            :key="biodataFormKey"
                            route-name="admin.users.edit"
                            :entity-id="record.id"
                            :locale="$page.props.user.origin_language_code"
                            @loaded-successfully="isFormBuilderShown = true"
                        >
                            <template #buttons>
                                <div class="field is-grouped is-grouped-right">
                                    <div class="control">
                                        <biz-button class="is-link">
                                            Update
                                        </biz-button>
                                    </div>
                                </div>
                            </template>
                        </form-builder>
                    </fieldset>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import FormBuilder from '@/Form/Builder';
    import FormUserPassword from '@/Pages/User/FormPassword';
    import FormUserProfile from '@/Pages/User/FormProfile';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
            BizButton,
            BizButtonLink,
            BizErrorNotifications,
            FormBuilder,
            FormUserPassword,
            FormUserProfile,
        },

        props: {
            can: { type: Object, required: true },
            countryOptions: { type: Array, default: () => [] },
            errors: { type: Object, default: () => {} },
            record: {type: Object, default: () => {} },
            roleOptions: { type: Array, default: () => [] },
            languageOptions: { type: Array, default: () => [] },
            title: { type: String, required: true },
        },

        setup(props) {
            const userProfileForm = {
                _method: 'put',
                first_name: props.record.first_name,
                last_name: props.record.last_name,
                email: props.record.email,
                photo: null,
                photo_url: props.record.profile_photo_url,
                profile_photo_media_id: props.record.profile_photo_media_id,
                country_code: props.record.country_code,
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
                isFormBuilderShown: false,
                isProcessing: false,
                loader: null,
            };
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
                    },
                    onFinish: () => {
                        self.loader.hide();
                        self.isProcessing = false;
                        self.profileForm.photo = null;
                        self.profileForm.profile_photo_media_id = self.record.profile_photo_media_id;

                        self.biodataFormKey += 1;
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
        },
    };
</script>
