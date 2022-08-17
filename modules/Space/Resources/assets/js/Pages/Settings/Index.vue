<template>
    <div>
        <div class="box mb-6">
            <space-type v-if="can.spaceType.view" />
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import SpaceType from './SpaceType';

    export default {
        components: {
            SpaceType,
        },

        layout: AppLayout,

        props: {
            can: { type: Object, required: true },
            title: { type: String, default: "" },
        },
    };
</script>
