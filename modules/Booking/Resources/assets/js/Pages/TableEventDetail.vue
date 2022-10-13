<template>
    <biz-table
        class="table"
        :class="{'is-fullwidth': isFullwidth}"
    >
        <tr>
            <th><biz-icon :icon="bookingIcon.calendar" /></th>
            <td>{{ event.start_end_time }}, {{ event.date }}</td>
        </tr>
        <tr>
            <th><biz-icon :icon="bookingIcon.timezone" /></th>
            <td>{{ event.timezone }} ({{ event.timezoneOffset }})</td>
        </tr>
        <tr>
            <th><biz-icon :icon="bookingIcon.duration" /></th>
            <td>{{ event.duration }}</td>
        </tr>

        <slot />
    </biz-table>
</template>

<script>
    import BizIcon from '@/Biz/Icon';
    import BizTable from '@/Biz/Table';
    import bookingIcon from '@booking/Libs/booking-icon';

    export default {
        components: {
            BizIcon,
            BizTable,
        },

        props: {
            isFullwidth: { type: Boolean, default: true },
            event: { type: Object, required: true },
        },

        setup(props) {
            return {
                bookingIcon,
            };
        },
    };
</script>
