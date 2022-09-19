<template>
    <div>
        <biz-widget-columns
            :widgets="widgets"
            :module-widgets="moduleWidgets"
        />
    </div>
</template>

<script>
    import BizWidgetColumns from '@/Biz/Widget/WidgetColumns';
    import Layout from '@/Layouts/User';

    export default {
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
