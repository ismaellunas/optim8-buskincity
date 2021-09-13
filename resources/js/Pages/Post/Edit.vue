<template>
<app-layout>
    <template #header>Post</template>

    <sdb-error-notifications :errors="$page.props.errors"/>

    <div class="box mb-6">
        <post-form
            v-model="form"
            :errors="errors"
            :is-new="false"
            :is-processing="isProcessing"
            :category-options="categoryOptions"
            :locale-options="localeOptions"
            :status-options="statusOptions"
            :cover-image="coverImage"
            @on-submit="onSubmit"
        />
    </div>
</app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import PostForm from '@/Pages/Post/Form';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import { map } from 'lodash';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';
    import { success as successAlert } from '@/Libs/alert';

    export default {
        components: {
            AppLayout,
            PostForm,
            SdbErrorNotifications,
        },
        props: {
            categoryOptions: Array,
            errors: Object,
            post: Object,
            statusOptions: Array,
            coverImage: Object,
        },
        setup(props, { emit }) {
            const defaultLocale = usePage().props.value.defaultLanguage;
            const postForm = {
                categories: map(props.post.categories, 'id'),
                content: props.post.content,
                cover_image_id: props.post.cover_image_id,
                excerpt: props.post.excerpt,
                locale: props.post.locale,
                meta_description: props.post.meta_description,
                meta_title: props.post.meta_title,
                slug: props.post.slug,
                status: props.post.status,
                title: props.post.title,
            };

            return {
                defaultLocale,
                form: useForm(postForm),
                localeOptions: usePage().props.value.languageOptions,
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
