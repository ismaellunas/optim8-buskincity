<template>
    <biz-table
        class="table"
        :class="{'is-fullwidth': isFullwidth}"
    >
        <tr>
            <th><biz-icon :icon="ecommerceIcon.calendar" /></th>
            <td>{{ event.start_end_time }}, {{ event.date }}</td>
        </tr>
        <tr>
            <th><biz-icon :icon="ecommerceIcon.timezone" /></th>
            <td>{{ event.timezone }} ({{ event.timezoneOffset }})</td>
        </tr>
        <tr>
            <th><biz-icon :icon="ecommerceIcon.duration" /></th>
            <td>{{ event.duration }}</td>
        </tr>

        <slot />
    </biz-table>
</template>

<script>
    import BizIcon from '@/Biz/Icon';
    import BizTable from '@/Biz/Table';
    import ecommerceIcon from '@ecommerce/Libs/ecommerce-icon';

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
                ecommerceIcon,
            };
        },
    };
</script>
