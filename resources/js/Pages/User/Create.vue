<template>
    <div>
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
                            :photo-url="photoUrl"
                            :language-options="supportedLanguageOptions"
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
                                    {{ i18n.cancel }}
                                </biz-button-link>
                            </div>
                            <div class="control">
                                <biz-button class="is-link">
                                    {{ i18n.create }}
                                </biz-button>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import FormUserPassword from '@/Pages/User/FormPassword.vue';
    import FormUserProfile from '@/Pages/User/FormProfile.vue';
    import { useForm } from '@inertiajs/vue3';
    import { success as successAlert } from '@/Libs/alert';

    export default {
        name: 'UserCreate',

        components: {
            BizButton,
            BizButtonLink,
            BizErrorNotifications,
            FormUserPassword,
            FormUserProfile,
        },

        mixins: [
            MixinHasLoader,
        ],

        provide() {
            return {
                i18n: this.i18n,
                instructions: this.instructions,
            };
        },

        layout: AppLayout,

        props: {
            errors: { type: Object, default: () => {} },
            supportedLanguageOptions: { type: Array, default: () => [] },
            record: {type: Object, default: () => {} },
            roleOptions: { type: Array, default: () => [] },
            title: { type: String, required: true },
            i18n: { type: Object, default: () => ({
                cancel : 'Cancel',
                Create : 'Create',
            }) },
            instructions: { type: Object, default: () => {} },
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
                language_id: props.record.language_id,
                photo: null,
                is_photo_deleted: false,
            };

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
                baseRouteName: 'admin.users',
                photoUrl: null,
                isProcessing: false,
            };
        },

        methods: {
            onSubmit() {
                const self = this;
                const form = this.form;
                form.post(route(this.baseRouteName+'.store'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.onStartLoadingOverlay();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        form.isDirty = false;
                        form.reset();

                        self.photoUrl = null;
                    },
                    onFinish: () => {
                        self.onEndLoadingOverlay();
                        self.isProcessing = false;
                    }
                });
            },
        },
    };
</script>
