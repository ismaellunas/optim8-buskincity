<template>
    <biz-table is-fullwidth>
        <tr>
            <th><biz-icon :icon="icon.duration" /></th>
            <td>{{ event.duration }}</td>
        </tr>
        <tr>
            <th><biz-icon :icon="icon.calendar" /></th>
            <td>{{ event.start_end_time }}, {{ event.date }}</td>
        </tr>
        <tr>
            <th><biz-icon :icon="icon.timezone" /></th>
            <td>{{ event.timezone }}</td>
        </tr>
    </biz-table>
</template>

<script>
    import BizIcon from '@/Biz/Icon.vue';
    import BizTable from '@/Biz/Table.vue';
    import { calendar, duration, timezone } from '@/Libs/icon-class';

    export default {
        components: {
            BizIcon,
            BizTable,
        },

        props: {
            event: { type: Object, required: true },
            isFullwidth: { type: Boolean, default: true },
        },

        setup() {
            return {
                icon: { calendar, duration, timezone },
            };
        },
    };
</script>
