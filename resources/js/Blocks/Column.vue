<template>
    <div class="column" :class="{'edit-mode-column': isEditMode}">
        <draggable
            :emptyInsertThreshold="emptyInsertThreshold"
            @change="log"
            class="dragArea list-group"
            group="components"
            item-key="id"
            v-model="components"
        >
            <template #item="{ element }">
                <component
                    :entityId="entityId"
                    :id="element.id"
                    :is="element.componentName"
                    :isEditMode="isEditMode"
                    @delete-content="deleteContent"
                    v-model="element.content"
                >
            </component>
            </template>
        </draggable>
    </div>
</template>

<script>
    import ContentCard from '@/Blocks/Contents/Card'
    import ContentCardText from '@/Blocks/Contents/CardText'
    import Draggable from 'vuedraggable';
    import H1 from '@/Blocks/Contents/H1';
    import H2 from '@/Blocks/Contents/H2';
    import Image from '@/Blocks/Contents/Image';
    import { useModelWrapper, isBlank } from '@/Libs/utils';
    import { usePage } from '@inertiajs/inertia-vue3'

    export default {
        components: {
            ContentCard,
            ContentCardText,
            Draggable,
            H1,
            H2,
            Image,
        },
        props: {
            id: {},
            isEditMode: {default: false},
            isDebugMode: {default: false},
            modelValue: {}, // components
        },
        setup(props, { emit }) {
            return {
                components: useModelWrapper(props, emit),
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
                    const removeIndex = this.components.map(block => block.id).indexOf(id);
                    this.components.splice(removeIndex, 1);
                }
                else {
                    console.log(id);
                }
            }
        }
    }
</script>
<style scoped>
.edit-mode-column {
    border: 1px #D3D3D3 dashed;
}
</style>
