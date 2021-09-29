<template>
<app-layout>
    <template #header>Add New User</template>

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
                    <user-profile-form
                        v-model="form"
                        :errors="errors"
                        :role-options="roleOptions"
                    ></user-profile-form>

                    <user-password-form
                        v-model="form"
                        :errors="errors"
                    ></user-password-form>

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
                                Create
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
            roleOptions: {},
        },
        setup(props) {
            const form = {
                name: null,
                email: null,
                role: null,
                password: null,
                password_confirmation: null,
            };

            return {
                form: useForm(form),
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
                const form = this.form;
                form.post(route(this.baseRouteName+'.store'), {
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
