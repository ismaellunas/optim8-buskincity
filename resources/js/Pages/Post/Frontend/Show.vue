<template>
    <Head>
        <title>{{ post.meta_title ?? '' }}</title>
        <meta head-key="description" name="description" :content="post.meta_description" />
    </Head>

    <div
        id="main-container"
        class="container mt-4"
    >
        <h1 class="title is-1 has-text-centered">{{ post.title }}</h1>
        <div
            class="content"
            v-html="post.content"
        >
        </div>
    </div>
</template>

<script>
    import PageLayout from '@/Layouts/PageLayout';
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        layout: PageLayout,
        components: {
            Head,
        },
        props: {
            currentLanguage: String,
            errors: Object,
            post: {},
        },
    }
</script>
