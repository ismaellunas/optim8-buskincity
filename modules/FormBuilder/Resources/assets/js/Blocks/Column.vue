<template>
    <div
        class="column break-long-text"
        :class="columnClass"
    >
        <draggable
            class="dragArea list-group"
            group="components"
            handle=".handle-content"
            item-key="id"
            :animation="300"
            :empty-insert-threshold="emptyInsertThreshold"
            :list="computedComponents"
            @change="log"
        >
            <template #item="{ element }">
                <component
                    :is="element.componentName"
                    :id="element.id"
                    v-model="computedDataEntities[element.id]"
                    class="component-configurable"
                    :data-id="element.id"
                    @delete-content="deleteContent"
                    @duplicate-content="duplicateContent"
                />
            </template>
        </draggable>
    </div>
</template>

<script>
    import Draggable from 'vuedraggable';
    import Email from './Inputs/Email';
    import Text from './Inputs/Text';
    import Textarea from './Inputs/Textarea';
    import Select from './Inputs/Select';
    import Number from './Inputs/Number';
    import { cloneDeep } from 'lodash';
    import { isBlank, useModelWrapper, generateElementId } from '@/Libs/utils';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'Column',

        components: {
            Draggable,
            Email,
            Text,
            Textarea,
            Select,
            Number,
        },

        props: {
            id: { type: String, required: true },
            isDebugMode: { type: Boolean, default: false },
            components: { type: Array, default: () => [] },
            dataEntities: { type: Object, default: () => {} },
        },

        setup(props, { emit }) {
            return {
                computedComponents: useModelWrapper(props, emit, 'components'),
                computedDataEntities: useModelWrapper(props, emit, 'dataEntities'),
                entityId: usePage().props.value.entityId ?? null,
            };
        },

        computed: {
            isEmptyComponents() {
                return !Array.isArray(this.computedComponents) || !this.computedComponents.length;
            },

            isDraggableEmpty() {
                return this.isEmptyComponents;
            },

            emptyInsertThreshold() {
                return this.isDraggableEmpty ? 50 : 5;
            },

            columnClass() {
                let classes = [];

                classes.push("edit-mode-column");

                return classes;
            },
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
                    this.computedComponents.splice(
                        this.computedComponents.map(block => block.id).indexOf(id),
                        1
                    );

                    delete this.computedDataEntities[id];
                }
            },

            duplicateContent(id) {
                if (!isBlank(id)) {
                    const duplicateComponent = cloneDeep(
                        this.computedComponents[
                            this.computedComponents.map(block => block.id).indexOf(id)
                        ]
                    );

                    duplicateComponent.id = generateElementId();

                    const duplicateEntity = cloneDeep(this.computedDataEntities[id]);
                    duplicateEntity.id = duplicateComponent.id;

                    this.computedDataEntities[duplicateComponent.id] = duplicateEntity;
                    this.computedComponents.push(duplicateComponent);
                }
            },
        }
    };
</script>