<template>
<app-layout>
    <template #header>{{ title }}</template>

    <sdb-error-notifications :errors="$page.props.errors"/>

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
                    <hr/>

                    <form-user-profile
                        v-model="profileForm"
                        :can-set-role="!record.isSuperAdministrator"
                        :role-options="roleOptions"
                    ></form-user-profile>

                    <div class="field is-grouped is-grouped-right">
                        <div class="control">
                            <sdb-button-link
                                :href="route(baseRouteName+'.index')"
                                class="is-link is-light">
                                Cancel
                            </sdb-button-link>
                        </div>
                        <div class="control">
                            <sdb-button class="is-link">
                                Update
                            </sdb-button>
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
                    <hr/>

                    <form-user-password
                        v-model="passwordForm"
                    ></form-user-password>

                    <div class="field is-grouped is-grouped-right">
                        <div class="control">
                            <sdb-button class="is-link">
                                Update
                            </sdb-button>
                        </div>
                    </div>
                </fieldset>
            </div>
        </form>

    </div>

</app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import FormUserPassword from '@/Pages/User/FormPassword';
    import FormUserProfile from '@/Pages/User/FormProfile';
    import { map } from 'lodash';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';
    import { success as successAlert } from '@/Libs/alert';

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbButtonLink,
            SdbErrorNotifications,
            FormUserPassword,
            FormUserProfile,
        },
        props: {
            errors: Object,
            record: Object,
            roleOptions: {},
            title: String,
        },
        setup(props, { emit }) {
            const user = props.record;
            const userProfileForm = {
                first_name: user.first_name,
                last_name: user.last_name,
                email: user.email,
            };

            if (!props.record.isSuperAdministrator) {
                userProfileForm['role'] = (user.roles[0]) ? user.roles[0].id : null;
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
            };
        },
        methods: {
            onSubmit() {
                const self = this;
                this.profileForm.put(route(this.baseRouteName+'.update', this.record.id), {
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

                form.put(route(this.baseRouteName+'.password', this.record.id), {
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
