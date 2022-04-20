<template>
    <layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ title }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <biz-widget-columns :widgets="widgets" />
                </div>
            </div>
        </div>
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
