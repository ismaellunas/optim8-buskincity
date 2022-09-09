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
    import AppLayout from '@/Layouts/AppLayout';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFlashNotifications from '@/Biz/FlashNotifications';
    import FormBuilder from './Form';
    import { onFormEditorClicked } from './../Libs/form-builder';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { ref, onMounted, onUnmounted } from 'vue';

    export default {
        name: 'Edit',

        components: {
            BizErrorNotifications,
            BizFlashNotifications,
            FormBuilder,
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