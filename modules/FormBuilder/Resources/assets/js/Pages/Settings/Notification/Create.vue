<template>
    <div>
        <biz-error-notifications
            :errors="$page.props.errors"
        />

        <notification-form
            v-model="form"
            :active-options="activeOptions"
            :field-name-options="fieldNameOptions"
            @on-submit="onSubmit"
        />
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import NotificationForm from './Form.vue';
    import { success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';
    import { provide } from 'vue';

    export default {
        name: 'SettingNotificationCreate',

        components: {
            BizErrorNotifications,
            NotificationForm,
        },

        mixins: [
            MixinHasLoader,
        ],

        provide() {
            return {
                i18n: this.i18n,
            };
        },

        layout: AppLayout,

        props: {
            activeOptions: { type: Array, default: () => [] },
            baseRouteName: { type: String, required: true },
            fieldNameOptions: { type: Array, default: () => [] },
            fieldNotes: { type: Object, default: () => {} },
            formBuilder: { type: Object, required: true },
            i18n: { type: Object, default: () => {} },
        },

        setup(props) {
            provide('formBuilderId', props.formBuilder.id);
            provide('fieldNotes', props.fieldNotes);

            return {
                form: useForm({
                    name: null,
                    send_to: null,
                    from_name: null,
                    from_email: null,
                    reply_to: null,
                    bcc: null,
                    subject: null,
                    message: null,
                    is_active: true,
                })
            };
        },

        methods: {
            onSubmit() {
                const self = this;

                self.form.post(
                    route(self.baseRouteName + '.store', this.formBuilder.id),
                    {
                        onStart: () => self.onStartLoadingOverlay(),
                        onFinish: () => self.onEndLoadingOverlay(),
                        onSuccess: (page) => successAlert(page.props.flash.message),
                    }
                )
            }
        },
    }
</script>