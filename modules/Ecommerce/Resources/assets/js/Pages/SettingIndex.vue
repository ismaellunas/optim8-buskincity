<template>
    <app-layout :title="title">
        <template #header>
            {{ title }}
        </template>

        <div>Setting Indexx</div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';

    export default {
        components: {
            AppLayout,
        },

        props: {
            title: { type: String, default: "Settings" },
        },
    };
</script>
