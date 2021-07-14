<template>
    <page-layout>
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

    export default {
        components: {
            BlockColumns,
            PageLayout,
        },
        props: {
            page: Object,
            errors: Object,
        },
    }
</script>
