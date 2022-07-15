<template>
    <draggable
        class="drag-area has-background-light"
        item-key="id"
        tag="ul"
        :list="spaces"
        :group="{ name: 'space' }"
        @end="onEnd"
    >
        <template #item="{ element }">
            <li :data-id="element.id">
                <space-item
                    :data-id="element.id"
                    :space="element"
                    :can-add-item="canAddItem(element)"
                    @delete-row="$emit('delete-row', $event)"
                />

                <nested-draggable
                    v-if="canAddItem(element)"
                    class="children"
                    :spaces="element.children"
                    :data-parent="element.id"
                    @on-end="onEnd"
                    @delete-row="$emit('delete-row', $event)"
                />
            </li>
        </template>
    </draggable>
</template>

<script>
    import Draggable from "vuedraggable";
    import SpaceItem from './SpaceItem';

    export default {
        name: "NestedDraggable",

        components: {
            Draggable,
            SpaceItem,
        },

        props: {
            spaces: { type: Array, default: () => [] },
            depth: { type: Number, default: 0 },
        },

        emits: [
            'on-end',
            'delete-row',
        ],

        methods: {
            canAddItem(element) {
                return element.depth < 2;
            },
            onEnd(evt) {
                this.$emit('on-end', evt);
            },
        },
    };
</script>

<style scoped>
.drag-area {
    min-height: 20px;
}
.drag-area .children {
    margin-left: 20px;
}
</style>
