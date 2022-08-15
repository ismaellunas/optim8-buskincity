<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <biz-widget-columns-admin :widgets="widgets" />
            </div>
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizWidgetColumnsAdmin from '@/Biz/Widget/WidgetColumnsAdmin';

    export default {
        name: 'Dashboard',

        components: {
            BizWidgetColumnsAdmin,
        },

        layout: AppLayout,

        props: {
            title: { type: String, required: true },
            widgets: { type: Array, default:() => [] },
        },
    };
</script>
