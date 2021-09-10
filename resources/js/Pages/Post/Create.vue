<template>
<app-layout>
    <template #header>Post</template>

    <sdb-error-notifications :errors="$page.props.errors"/>

    <div class="box mb-6">
        <post-form
            v-model="form"
            :errors="errors"
            :is-new="true"
            :status-options="statusOptions"
            :locale-options="localeOptions"
            :category-options="categoryOptions"
            @on-submit="onSubmit"
        />
    </div>
</app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import PostForm from '@/Pages/Post/Form';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';

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
        },
        setup(props) {
            const defaultLocale = usePage().props.value.defaultLanguage;

            const postForm = {
                categories: [],
                content: null,
                cover_image_id: null,
                excerpt: null,
                locale: defaultLocale,
                meta_description: null,
                meta_title: null,
                slug: '',
                status: 0,
                title: '',
            };

            return {
                defaultLocale,
                form: useForm(postForm),
                loader: null,
                localeOptions: usePage().props.value.languageOptions,
            };
        },
        data() {
            return {
                baseRouteName: 'admin.posts',
            };
        },
        methods: {
            onSubmit() {
                const self = this;
                this.form.post(route(this.baseRouteName+'.store'), {
                    onStart: () => {
                        self.loader = self.$loading.show();
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onFinish: () => {
                        self.loader.hide();
                    }
                });
            },
        },
    };
</script>
