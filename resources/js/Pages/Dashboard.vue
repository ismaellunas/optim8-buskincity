<template>
    <layout>
        <template #header>
            <h1 class="title is-2">
                {{ title }}
            </h1>
        </template>

        <biz-widget-columns :widgets="widgets" />
    </layout>
</template>

<script>
    import BizWidgetColumns from '@/Biz/Widget/WidgetColumns';
    import Layout from '@/Layouts/User';

    export default {
        components: {
            BizWidgetColumns,
            Layout,
        },

        props: {
            title: { type: String, default: 'Dashboard' },
            widgets: { type: Array, default:() => [] },
        },
    };
</script>
