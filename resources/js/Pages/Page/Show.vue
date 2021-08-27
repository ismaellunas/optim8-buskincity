<template>
    <Head>
        <title>{{page.meta_title}}</title>
        <meta head-key="description" name="description" :content="page.meta_description" />
    </Head>

    <div id="main-container" class="container mt-4">
        <template v-for="(block, index) in page.data.structures">
            <block-columns
                v-model="page.data.structures[index]"
                v-model:data-entities="page.data.entities"
                :isEditMode="false"
                :id="block.id"
                :images="images"
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
            page: Object,
            errors: Object,
            images: {},
        },
    }
</script>
