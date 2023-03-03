<template>
    <div>
        <biz-widget-columns
            :widgets="widgets"
            :module-widgets="moduleWidgets"
        />
    </div>
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
            moduleWidgets: { type: Array, default:() => [] },
            widgets: { type: Array, default:() => [] },
        },
    };
</script>
