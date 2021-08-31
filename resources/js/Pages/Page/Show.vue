<template>
    <Head>
        <title>{{page.meta_title}}</title>
        <meta head-key="description" name="description" :content="page.meta_description" />
    </Head>

    <div id="main-container" class="container mt-4">
        <template v-for="(block, index) in page.data.structures">
            <block-columns
                :id="block.id"
                v-model="page.data.structures[index]"
                v-model:data-entities="page.data.entities"
                :is-edit-mode="false"
                :selected-locale="currentLanguage"
                />
        </template>
    </div>

</template>

<script>
    import BlockColumns from '@/Blocks/Columns';
    import PageLayout from '@/Layouts/PageLayout';
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        layout: PageLayout,
        components: {
            BlockColumns,
            Head,
        },
        props: {
            currentLanguage: String,
            errors: Object,
            images: {},
            page: Object,
        },
    }
</script>
