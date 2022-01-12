<template>
    <app-layout>
        <template #header>{{ title }}</template>

        <biz-error-notifications :errors="$page.props.errors" />

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

            <div class="columns">
                <div class="column">
                    <fieldset
                        class="box"
                        :disabled="isProcessing"
                    >
                        <h3 class="title is-3">
                            {{ formTitle }}
                        </h3>

                        <hr>

                        <form-biodata
                            route-name="admin.users.edit"
                            :entity-id="record.id"
                            button-label="Update"
                            button-group-align="right"
                            button-class="is-link"
                            @loaded-successfully="onFormLoadedSuccessfully"
                        />
                    </fieldset>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import FormUserPassword from '@/Pages/User/FormPassword';
    import FormUserProfile from '@/Pages/User/FormProfile';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import { map } from 'lodash';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';
    import FormBiodata from '@/Form/FormBuilder';

    export default {
        components: {
            AppLayout,
            FormUserPassword,
            FormUserProfile,
            BizButton,
            BizButtonLink,
            BizErrorNotifications,
            FormBiodata,
        },

        props: {
            errors: { type: Object, default: () => {} },
            record: {type: Object, default: () => {} },
            roleOptions: { type: Array, default: () => [] },
            title: { type: String, required: true },
        },

        setup(props, { emit }) {
            const userProfileForm = {
                first_name: props.record.first_name,
                last_name: props.record.last_name,
                email: props.record.email,
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
                isProcessing: false,
                loader: null,
                formName: 'biodata',
                formTitle: '',
            };
        },

        methods: {
            onSubmit() {
                const self = this;
                self.profileForm.put(route(self.baseRouteName+'.update', self.record.id), {
                    preserveScroll: false,
                    onStart: () => {
                        self.loader = self.$loading.show();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        self.profileForm.isDirty = false;
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

            onFormLoadedSuccessfully(schema) {
                this.formTitle = schema.title;
            }
        },
    };
</script>
