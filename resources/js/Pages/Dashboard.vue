<template>
    <biz-widget-columns
        :widgets="widgets"
    />
</template>

<script>
    import Layout from '@/Layouts/User.vue';
    import BizWidgetColumns from '@/Biz/Widget/WidgetColumns.vue';

    export default {
        name: 'DashboardDefault',

        components: {
            BizWidgetColumns,
        },

        layout: Layout,

        props: {
            widgets: { type: Array, default:() => [] },
        },
    };
</script>
