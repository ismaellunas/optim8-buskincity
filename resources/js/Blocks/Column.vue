<template>
    <div class="column" :class="columnClass">
        <draggable
            v-if="isEditMode"
            :list="components"
            :empty-insert-threshold="emptyInsertThreshold"
            animation="300"
            class="dragArea list-group"
            group="components"
            handle=".handle-content"
            item-key="id"
            @change="log"
        >
            <template #item="{ element, index }">
                <component
                    :is="element.componentName"
                    :id="element.id"
                    v-model="dataEntities[element.id]"
                    :is-edit-mode="isEditMode"
                    class="page-component"
                    @click="settingContent(element.id)"
                    @delete-content="deleteContent"
                />
            </template>
        </draggable>

        <template v-else>
            <component
                :is="element.componentName"
                v-for="(element, index) in components"
                :id="element.id"
                v-model="dataEntities[element.id]"
                :is-edit-mode="isEditMode"
            />
        </template>
    </div>
</template>

<script>
    import Card from '@/Blocks/Contents/Card';
    import CardText from '@/Blocks/Contents/CardText';
    import Draggable from 'vuedraggable';
    import Heading from '@/Blocks/Contents/Heading';
    import Image from '@/Blocks/Contents/Image';
    import Text from '@/Blocks/Contents/Text';
    import { useModelWrapper, isBlank } from '@/Libs/utils';
    import { usePage } from '@inertiajs/inertia-vue3'

    export default {
        components: {
            Card,
            CardText,
            Draggable,
            Heading,
            Image,
            Text,
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
            },
            columnClass() {
                let classes = [];
                if (this.isEditMode) {
                    classes.push("edit-mode-column");
                }
                return classes;
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
