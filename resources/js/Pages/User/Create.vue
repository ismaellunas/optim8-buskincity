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
                        <form-user-profile
                            v-model="form"
                            :role-options="roleOptions"
                        />

                        <form-user-password
                            v-model="form"
                        />

                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <biz-button-link
                                    :href="route(baseRouteName+'.index')"
                                    class="is-link is-light"
                                >
                                    Cancel
                                </biz-button-link>
                            </div>
                            <div class="control">
                                <biz-button class="is-link">
                                    Create
                                </biz-button>
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
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import FormUserPassword from '@/Pages/User/FormPassword';
    import FormUserProfile from '@/Pages/User/FormProfile';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { success as successAlert } from '@/Libs/alert';

    export default {
        components: {
            AppLayout,
            BizButton,
            BizButtonLink,
            BizErrorNotifications,
            FormUserPassword,
            FormUserProfile,
        },
        props: {
            errors: Object,
            roleOptions: {},
            title: String,
        },
        setup(props) {
            const form = {
                first_name: null,
                last_name: null,
                email: null,
                role: null,
                password: null,
                password_confirmation: null,
                country_code: null,
                language_id: null,
                photo: null,
                photo_url: null,
                profile_photo_media_id: null,
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
