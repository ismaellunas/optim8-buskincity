<template>
    <layout>
        <Head title="Dashboard" />

        <template #header>
            <h1 class="title is-2">
                {{ title }}
            </h1>
        </template>

        <template
            v-if="description"
            #headerDescription
        >
            <p>{{ description }}</p>
        </template>

        <biz-widget-columns :widgets="widgets" />
    </layout>
</template>

<script>
    import BizWidgetColumns from '@/Biz/Widget/WidgetColumns';
    import Layout from '@/Layouts/User';
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizWidgetColumns,
            Head,
            Layout,
        },

        props: {
            title: { type: String, default: 'Dashboard' },
            widgets: { type: Array, default:() => [] },
            description: {type: String, default: null},
        },
    };
</script>
