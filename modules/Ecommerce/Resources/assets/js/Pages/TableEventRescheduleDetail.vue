<template>
    <biz-table :class="{'is-fullwidth': isFullwidth}">
        <tr>
            <th><biz-icon :icon="ecommerceIcon.duration" /></th>
            <td>{{ event.duration }}</td>
        </tr>
        <tr>
            <th><biz-icon :icon="ecommerceIcon.calendar" /></th>
            <td>{{ event.start_end_time }}, {{ event.date }}</td>
        </tr>
        <tr>
            <th><biz-icon :icon="ecommerceIcon.timezone" /></th>
            <td>{{ event.timezone }}</td>
        </tr>
    </biz-table>
</template>

<script>
    import BizIcon from '@/Biz/Icon';
    import BizTable from '@/Biz/Table';
    import ecommerceIcon from '@mod/Ecommerce/Resources/assets/js/Libs/ecommerce-icon';

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
                ecommerceIcon,
            };
        },
    };
</script>
