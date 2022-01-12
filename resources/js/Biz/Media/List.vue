<template>
    <biz-table class="is-striped is-hoverable is-fullwidth">
        <thead>
            <tr>
                <th></th>
                <th>File Name</th>
                <th>Date Modified</th>
                <th>Type</th>
                <th>Size</th>
                <th>
                    <div class="level-right">Actions</div>
                </th>
            </tr>
        </thead>
        <tbody>
            <template
                v-for="medium in media"
                :key="medium.id"
            >
                <slot :medium="medium"></slot>
            </template>
        </tbody>
    </biz-table>
</template>

<script>
    import BizTable from '@/Biz/Table';

    export default {
        name: 'MediaList',
        components: {
            BizTable,
        },
        props: {
            media: {},
        },
    };
</script>
