<template>
    <div>
        <biz-widget-columns :widgets="widgets" />
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
            widgets: { type: Array, default:() => [] },
        },
    };
</script>
