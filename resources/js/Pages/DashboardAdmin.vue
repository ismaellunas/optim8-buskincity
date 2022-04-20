<template>
    <app-layout>
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
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizWidgetColumns from '@/Biz/Widget/WidgetColumns';

    export default {
        components: {
            AppLayout,
            BizWidgetColumns,
        },

        props: {
            title: { type: String, default: 'Dashboard' },
            widgets: { type: Array, default:() => [] },
        },
    };
</script>
