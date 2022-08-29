<template>
    <div>
        <biz-error-notifications
            :errors="$page.props.errors"
        />

        <biz-flash-notifications :flash="$page.props.flash" />

        <div class="box">
            <form-builder-form
                v-model="form"
                v-model:content-config-id="contentConfigId"
            />
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFlashNotifications from '@/Biz/FlashNotifications';
    import FormBuilderForm from './Form';
    import { onFormEditorClicked } from './../Libs/form-builder';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { ref, onMounted, onUnmounted } from 'vue';

    export default {
        name: 'Edit',

        components: {
            BizErrorNotifications,
            BizFlashNotifications,
            FormBuilderForm,
        },

        provide: {
            isEditMode: true,
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            formBuilder: { type: Object, required: true },
        },

        setup(props) {
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
                form: useForm(props.formBuilder)
            };
        },
    }
</script>