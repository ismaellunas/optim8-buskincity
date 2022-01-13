<template>
    <biz-table class="is-striped is-hoverable is-fullwidth">
        <tbody>
            <template
                v-for="record in records"
                :key="record.id"
            >
                <slot :record="record"></slot>
            </template>
        </tbody>
    </biz-table>
</template>

<script>
    import BizTable from '@/Biz/Table';

    export default {
        name: 'PostList',
        components: {
            BizTable,
        },
        props: [
           'records',
        ],
    };
</script>
