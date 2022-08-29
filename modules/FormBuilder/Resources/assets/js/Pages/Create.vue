<template>
    <div>
        <biz-error-notifications
            :errors="$page.props.errors"
        />

        <div class="box">
            <form-builder-form
                v-model="formBuilder"
                v-model:content-config-id="contentConfigId"
            />
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import FormBuilderForm from './Form';
    import { getEmptyForm } from './../Libs/form';
    import { onFormEditorClicked } from './../Libs/form-builder';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { ref, onMounted, onUnmounted } from 'vue';

    export default {
        name: 'Create',

        components: {
            BizErrorNotifications,
            FormBuilderForm,
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
        },

        setup() {
            const contentConfigId = ref('');

            function pageListener(event) {
                onFormEditorClicked(event, contentConfigId);
            }

            onMounted(() => {
                window.addEventListener("click", pageListener);
            });

            onUnmounted(() => {
                window.removeEventListener("click", pageListener);
            });

            return {
                contentConfigId,
                formBuilder: useForm(getEmptyForm())
            };
        },
    }
</script>