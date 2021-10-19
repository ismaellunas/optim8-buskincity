<template>
    <div class="column">
        <component
            :is="element.componentName"
            v-for="(element) in components"
            :key="element.id"
            :id="element.id"
            v-model="dataEntities[element.id]"
            :selected-locale="selectedLocale"
        />
    </div>
</template>

<script>
    import Card from '@/Blocks/Frontend/Contents/Card';
    import CardText from '@/Blocks/Frontend/Contents/CardText';
    import { usePage } from '@inertiajs/inertia-vue3'

    export default {
        name: 'BlockColumn',
        components: {
            Card,
            CardText,
        },
        props: {
            components: {type: Array, default: []},
            dataEntities: {type: Object, default: {}},
            id: String,
            selectedLocale: String,
        },
        setup() {
            return {
                entityId: usePage().props.value.entityId ?? null,
            };
        },
    }
</script>
