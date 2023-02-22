<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

        <div class="mb-6">
            <post-form
                v-model="form"
                :can="can"
                :errors="errors"
                :is-new="false"
                :is-processing="isProcessing"
                :category-options="categoryOptions"
                :locale-options="localeOptions"
                :status-options="statusOptions"
                :cover-image="coverImage"
                :modules="modules"
                @on-submit="onSubmit"
            />
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import PostForm from '@/Pages/Post/Form.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import { map, find } from 'lodash';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';
    import { success as successAlert } from '@/Libs/alert';

    export default {
        name: 'PostEdit',
        components: {
            PostForm,
            BizErrorNotifications,
        },
        layout: AppLayout,
        props: {
            can: { type: Object, required: true },
            categoryOptions: { type: Array, required: true, },
            coverImage: { type: [Object, null], required: true },
            errors: { type: Object, default:() => {} },
            languageOptions: { type: Object, required: true },
            post: { type: Object, required: true },
            statusOptions: { type: Array, required: true, },
            title: { type: String, required: true },
            modules: { type: Object, default: () => {} },
        },
        setup(props) {
            const defaultLocale = usePage().props.value.defaultLanguage;
            const primaryCategory = find(props.post.categories, function (category) {
                return category.pivot.is_primary;
            });
            const postForm = {
                categories: map(props.post.categories, 'id'),
                primary_category: primaryCategory?.id ?? null,
                content: props.post.content,
                cover_image_id: props.post.cover_image_id,
                excerpt: props.post.excerpt,
                locale: props.post.locale,
                meta_description: props.post.meta_description,
                meta_title: props.post.meta_title,
                slug: props.post.slug,
                status: props.post.status,
                title: props.post.title,
                scheduled_at: (
                    (props.post.scheduled_at)
                        ? new Date(props.post.scheduled_at)
                        : new Date()
                ),
            };

            return {
                defaultLocale,
                form: useForm(postForm),
                localeOptions: props.languageOptions,
            };
        },
        data() {
            return {
                baseRouteName: 'admin.posts',
                isProcessing: false,
                loader: null,
            };
        },
        methods: {
            onSubmit() {
                const self = this;
                this.form.put(route(this.baseRouteName+'.update', this.post.id), {
                    preserveScroll: false,
                    onStart: () => {
                        self.loader = self.$loading.show();
                        self.isProcessing = true;
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                        self.form.slug = page.props.post.slug;
                        self.form.isDirty = false;
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
