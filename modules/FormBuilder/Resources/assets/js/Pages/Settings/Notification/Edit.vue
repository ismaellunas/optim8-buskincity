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
        name: 'SettingNotificationEdit',

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
            settingNotification: { type: Object, required: true },
            i18n: { type: Object, default: () => {} },
        },

        setup(props) {
            provide('formBuilderId', props.formBuilder.id);
            provide('fieldNotes', props.fieldNotes);
            provide('isEditMode', true);

            return {
                form: useForm(props.settingNotification)
            };
        },

        methods: {
            onSubmit() {
                const self = this;

                self.form.put(
                    route(self.baseRouteName + '.update', {
                        'form_builder': self.formBuilder.id,
                        'notification': self.settingNotification.id
                    }),
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