<template>
<app-layout>
    <template #header>User</template>

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

                    <user-profile-form
                        v-model="profileForm"
                        :errors="errors"
                        :is-new="false"
                        :is-processing="isProcessing"
                        :base-route-name="baseRouteName"
                        :role-options="roleOptions"
                        @on-submit="onSubmit"
                    ></user-profile-form>

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

                    <user-password-form
                        v-model="passwordForm"
                        :errors="passwordForm.errors"
                        :is-processing="isProcessing"
                        @on-submit="onPasswordSubmit"
                    ></user-password-form>
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
    import UserPasswordForm from '@/Pages/User/PasswordForm';
    import UserProfileForm from '@/Pages/User/ProfileForm';
    import { map } from 'lodash';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';
    import { success as successAlert } from '@/Libs/alert';

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbButtonLink,
            SdbErrorNotifications,
            UserPasswordForm,
            UserProfileForm,
        },
        props: {
            errors: Object,
            record: Object,
            roleOptions: {},
        },
        setup(props, { emit }) {
            const user = props.record;
            const userProfileForm = {
                name: user.name,
                email: user.email,
                role: user.roles[0] ?? null,
            };

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
