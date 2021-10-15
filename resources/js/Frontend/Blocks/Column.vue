<template>
    <div class="column">
        <component
            :is="element.componentName"
            v-for="(element, index) in components"
            :id="element.id"
            v-model="dataEntities[element.id]"
            :data-media="dataMedia"
            :selected-locale="selectedLocale"
        />
    </div>
</template>

<script>
    import Card from '@/Frontend/Blocks/Contents/Card';
    import { usePage } from '@inertiajs/inertia-vue3'

    export default {
        name: 'BlockColumn',
        components: {
            Card,
        },
        props: {
            id: {},
            components: {type: Array, default: []},
            dataEntities: {},
            dataMedia: {},
            selectedLocale: String,
        },
        setup(props, { emit }) {
            return {
                entityId: usePage().props.value.entityId ?? null,
            };
        },
        computed: {
            //
        },
        methods: {
            //
        }
    }
</script>
