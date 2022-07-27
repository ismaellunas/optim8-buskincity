<template>
    <app-layout :title="title">
        <template #header>
            {{ title }}
        </template>

        <div class="box mb-6">
            <space-type v-if="can.spaceType.view" />
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import SpaceType from './SpaceType';

    export default {
        components: {
            AppLayout,
            SpaceType,
        },

        props: {
            can: { type: Object, required: true },
            title: { type: String, default: "" },
        },
    };
</script>
