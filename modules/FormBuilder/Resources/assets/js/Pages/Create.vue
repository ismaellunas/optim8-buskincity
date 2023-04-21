<template>
    <div>
        <biz-error-notifications
            :errors="$page.props.errors"
        />

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
    import FormBuilder from './Form.vue';
    import { getEmptyForm } from './../Libs/form';
    import { onFormEditorClicked } from './../Libs/form-builder';
    import { useForm } from '@inertiajs/vue3';
    import { ref, onMounted, onUnmounted } from 'vue';

    export default {
        name: 'FormBuilderCreate',

        components: {
            BizErrorNotifications,
            FormBuilder,
        },

        provide() {
            return {
                i18n: this.i18n,
            };
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            i18n: { type: Object, default: () => {} }
        },

        setup() {
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
                form: useForm(getEmptyForm())
            };
        },
    }
</script>