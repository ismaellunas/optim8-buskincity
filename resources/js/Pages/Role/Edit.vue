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
                                    {{ i18n.update }}
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
    import FormRole from '@/Pages/Role/Form.vue';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';

    export default {
        name: 'RoleEdit',

        components: {
            BizButton,
            BizButtonLink,
            BizErrorNotifications,
            FormRole,
        },

        mixins: [
            MixinHasLoader,
        ],

        provide() {
            return {
                can: this.can,
            };
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            can: { type: Object, required: true },
            errors: { type: Object, default: () => {} },
            permissions: { type: Object, default: () => {} },
            record: { type: Object, required: true },
            title: { type: String, required: true },
            i18n: { type: Object, default: () => ({
                cancel : 'Cancel',
                update : 'Update',
            }) },
        },

        setup(props) {
            const form = {
                name: props.record.name,
                permissions: (props.record.permissions
                    ? props.record.permissions.map(permission => permission.name)
                    : []
                ),
            };

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
                isProcessing: false,
            };
        },

        methods: {
            onSubmit() {
                const self = this;
                const form = self.form;

                form.put(route(self.baseRouteName+'.update', self.record.id), {
                    preserveScroll: false,
                    onStart: () => {
                        self.onStartLoadingOverlay();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        form.isDirty = false;
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
