<template>
    <div>
        <biz-error-notifications
            :errors="$page.props.errors"
        />

        <div class="box">
            <form-builder
                v-model="formBuilder"
                v-model:input-config-id="inputConfigId"
            />
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import FormBuilder from './Form';
    import { getEmptyForm } from './../Libs/form';
    import { onFormEditorClicked } from './../Libs/form-builder';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { ref, onMounted, onUnmounted } from 'vue';

    export default {
        name: 'Create',

        components: {
            BizErrorNotifications,
            FormBuilder,
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
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
                formBuilder: useForm(getEmptyForm())
            };
        },
    }
</script>