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
                        <form-role
                            v-model="form"
                            :errors="errors"
                            :permission-options="permissions"
                        />

                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <biz-button-link
                                    class="is-link is-light"
                                    :href="route(baseRouteName+'.index')"
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
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import FormRole from '@/Pages/Role/Form.vue';
    import { useForm } from '@inertiajs/vue3';
    import { success as successAlert } from '@/Libs/alert';

    export default {
        name: 'RoleCreate',

        components: {
            BizButton,
            BizButtonLink,
            BizErrorNotifications,
            FormRole,
        },

        provide() {
            return {
                i18n: this.i18n,
                can: this.can,
            };
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            errors: { type: Object, default: () => {} },
            permissions: { type: Object, default: () => {} },
            title: { type: String, required: true },
            can: { type: Object, required: true },
            i18n: { type: Object, default: () => ({
                cancel : 'Cancel',
                create : 'Create',
            }) },
        },

        setup(props) {
            const form = {
                name: null,
                permissions: [],
            };

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
                isProcessing: false,
                loader: null,
            };
        },

        methods: {
            onSubmit() {
                const self = this;
                const form = self.form;

                form.post(route(this.baseRouteName+'.store'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.loader = self.$loading.show();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        form.isDirty = false;
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
