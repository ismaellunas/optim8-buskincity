<template>
    <div>
        <biz-error-notifications
            :errors="$page.props.errors"
        />

        <biz-flash-notifications :flash="$page.props.flash" />

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
    import AppLayout from '@/Layouts/AppLayout';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFlashNotifications from '@/Biz/FlashNotifications';
    import NotificationForm from './NotificationForm';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { provide } from 'vue'

    export default {
        name: 'Edit',

        components: {
            BizErrorNotifications,
            BizFlashNotifications,
            NotificationForm,
        },

        mixins: [
            MixinHasLoader,
        ],

        layout: AppLayout,

        props: {
            activeOptions: { type: Array, default: () => [] },
            baseRouteName: { type: String, required: true },
            fieldNameOptions: { type: Array, default: () => [] },
            fieldNotes: { type: Object, default: () => {} },
            formBuilder: { type: Object, required: true },
            settingNotification: { type: Object, required: true },
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
                    }
                )
            }
        },
    }
</script>