<template>
    <div>
        <biz-error-notifications
            :errors="$page.props.errors"
        />

        <biz-flash-notifications :flash="$page.props.flash" />

        <div class="box">
            <form-builder
                v-model="form"
                v-model:input-config-id="inputConfigId"
            />
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFlashNotifications from '@/Biz/FlashNotifications.vue';
    import FormBuilder from './Form.vue';
    import { onFormEditorClicked } from './../Libs/form-builder';
    import { useForm } from '@inertiajs/vue3';
    import { ref, onMounted, onUnmounted } from 'vue';

    export default {
        name: 'FormBuilderEdit',

        components: {
            BizErrorNotifications,
            BizFlashNotifications,
            FormBuilder,
        },

        provide() {
            return {
                isEditMode: true,
                i18n: this.i18n,
            };
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            formBuilder: { type: Object, required: true },
            i18n: { type: Object, default: () => {} }
        },

        setup(props) {
            const inputConfigId = ref('');

            function pageListener(event) {
                onFormEditorClicked(event, inputConfigId);
            }

            onMounted(() => {
                window.addEventListener("click", pageListener);
            });

            onUnmounted(() => {
                window.removeEventListener("click", pageListener);
            });

            return {
                inputConfigId,
                form: useForm(props.formBuilder)
            };
        },
    }
</script>