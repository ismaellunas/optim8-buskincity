<template>
    <div class="column" :class="{'edit-mode-column': isEditMode}">
        <draggable
            :list="components"
            @change="log"
            animation="300"
            class="dragArea list-group"
            empty-insert-threshold="5"
            group="components"
            handle=".handle-content"
            item-key="id"
        >
            <template #item="{ element, index }">
                <component
                    :id="element.id"
                    :is="element.componentName"
                    :isEditMode="isEditMode"
                    @delete-content="deleteContent"
                    @setting-content="settingContent"
                    v-model="dataEntities[element.id]"
                >
            </component>
            </template>
        </draggable>
    </div>
</template>

<script>
    import Card from '@/Blocks/Contents/Card';
    import CardText from '@/Blocks/Contents/CardText';
    import Draggable from 'vuedraggable';
    import Heading from '@/Blocks/Contents/Heading';
    import Image from '@/Blocks/Contents/Image';
    import { useModelWrapper, isBlank } from '@/Libs/utils';
    import { usePage } from '@inertiajs/inertia-vue3'

    export default {
        components: {
            Card,
            CardText,
            Draggable,
            Heading,
            Image,
        },
        props: {
            id: {},
            isEditMode: {default: false},
            isDebugMode: {default: false},
            components: {type: Array, default: []},
            dataEntities: {},
        },
        setup(props, { emit }) {
            return {
                entityId: usePage().props.value.entityId ?? null,
            };
        },
        computed: {
            isEmptyComponents() {
                return !Array.isArray(this.components) || !this.components.length;
            },
            isDraggableEmpty() {
                return this.isEditMode && this.isEmptyComponents;
            },
            emptyInsertThreshold() {
                return this.isDraggableEmpty ? 50 : 5;
            }
        },
        methods: {
            log: function(evt) {
                if (this.isDebugMode) {
                    window.console.log(evt);
                }
            },
            cloneComponent(content) {
                let clonedContent = JSON.parse(JSON.stringify(content));
                clonedContent.id = generateElementId();
                return clonedContent;
            },
            deleteContent(id) {
                if (!isBlank(id)) {
                    this.components.splice(
                        this.components.map(block => block.id).indexOf(id),
                        1
                    );

                    delete this.dataEntities[id];

                    this.settingContent('');
                }
            },
            settingContent(event) {
                this.$emit('setting-content', event)
            },
        }
    }
</script>
<style scoped>
.edit-mode-column {
    border: 1px #D3D3D3 dashed;
}
</style>
