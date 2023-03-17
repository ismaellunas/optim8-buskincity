<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

        <div class="mb-6">
            <post-form
                v-model="form"
                :can="can"
                :errors="errors"
                :is-new="true"
                :is-processing="isProcessing"
                :status-options="statusOptions"
                :locale-options="localeOptions"
                :category-options="categoryOptions"
                :modules="modules"
                :instructions="instructions"
                @on-submit="onSubmit"
            />
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import PostForm from '@/Pages/Post/Form.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import { useForm, usePage } from '@inertiajs/vue3';
    import { success as successAlert } from '@/Libs/alert';

    export default {
        name: 'PostCreate',
        components: {
            PostForm,
            BizErrorNotifications,
        },
        layout: AppLayout,
        props: {
            can: { type: Object, required: true },
            categoryOptions: { type: Array, default:() => [] },
            errors: { type: Object, default:() => {} },
            languageOptions: { type: Object, required: true },
            post: { type: Object, required: true },
            statusOptions: { type: Array, default:() => [] },
            title: { type: String, required: true },
            modules: { type: Object, default: () => {} },
            instructions: { type: Object, default: () => {} },
        },
        setup(props) {
            const defaultLocale = usePage().props.defaultLanguage;

            const postForm = {
                categories: [],
                primary_category: null,
                content: null,
                cover_image_id: null,
                excerpt: null,
                locale: defaultLocale,
                meta_description: null,
                meta_title: null,
                slug: '',
                status: 0,
                title: '',
                scheduled_at: null,
            };

            return {
                defaultLocale,
                form: useForm(postForm),
                loader: null,
                localeOptions: props.languageOptions,
            };
        },
        data() {
            return {
                baseRouteName: 'admin.posts',
                isProcessing: false,
            };
        },
        methods: {
            onSubmit() {
                const self = this;
                this.form.post(route(this.baseRouteName+'.store'), {
                    onStart: () => {
                        self.loader = self.$loading.show();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onFinish: () => {
                        self.loader.hide();
                        self.isProcessing = false;
                    }
                });
            },
        },
    };
</script>
