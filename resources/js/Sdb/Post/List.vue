<template>
    <sdb-table class="is-striped is-hoverable is-fullwidth">
        <tbody>
            <template
                v-for="record in records"
                :key="record.id"
            >
                <slot :record="record"></slot>
            </template>
        </tbody>
    </sdb-table>
</template>

<script>
    import SdbTable from '@/Sdb/Table';

    export default {
        name: 'PostList',
        components: {
            SdbTable,
        },
        props: [
           'records',
        ],
    };
</script>
