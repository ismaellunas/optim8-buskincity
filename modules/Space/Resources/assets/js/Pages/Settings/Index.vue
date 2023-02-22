<template>
    <div>
        <div class="box mb-6">
            <space-type v-if="can.spaceType.view" />
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import SpaceType from './SpaceType.vue';

    export default {
        name: 'SettingIndex',

        components: {
            SpaceType,
        },

        provide() {
            return {
                i18n: this.i18n,
            };
        },

        layout: AppLayout,

        props: {
            can: { type: Object, required: true },
            title: { type: String, default: "" },
            i18n: { type: Object, default: () => {} },
        },
    };
</script>
