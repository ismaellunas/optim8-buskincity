<template>
    <page-layout>
        <Head>
            <title>{{page.meta_title}}</title>
             <meta head-key="description" name="description" :content="page.meta_description" />
        </Head>
        <template v-for="(block, index) in page.data">
            <block-columns
                v-model="page.data[index]"
                :isEditMode="false"
                :id="block.id"
                />
        </template>
    </page-layout>
</template>

<script>
    import BlockColumns from '@/Blocks/Columns';
    import PageLayout from '@/Layouts/PageLayout';
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BlockColumns,
            PageLayout,
            Head,
        },
        props: {
            page: Object,
            errors: Object,
        },
    }
</script>
