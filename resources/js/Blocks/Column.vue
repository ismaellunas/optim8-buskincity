<template>
    <div
        class="column break-long-text"
        :class="columnClass"
    >
        <draggable
            v-if="isEditMode"
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
                    class="page-component"
                    :can="can"
                    :is-edit-mode="isEditMode"
                    :data-media="dataMedia"
                    :selected-locale="selectedLocale"
                    @click="settingContent(element.id)"
                    @delete-content="deleteContent"
                    @duplicate-content="duplicateContent"
                />
            </template>
        </draggable>

        <template v-else>
            <component
                :is="element.componentName"
                v-for="element in computedComponents"
                :id="element.id"
                :key="element.id"
                v-model="computedDataEntities[element.id]"
                :data-media="dataMedia"
                :is-edit-mode="isEditMode"
                :selected-locale="selectedLocale"
            />
        </template>
    </div>
</template>

<script>
    import Button from '@/Blocks/Contents/Button';
    import Card from '@/Blocks/Contents/Card';
    import CardText from '@/Blocks/Contents/CardText';
    import Carousel from '@/Blocks/Contents/Carousel';
    import Draggable from 'vuedraggable';
    import Faq from '@/Blocks/Contents/Faq';
    import Heading from '@/Blocks/Contents/Heading';
    import Icon from '@/Blocks/Contents/Icon';
    import Image from '@/Blocks/Contents/Image';
    import Tabs from '@/Blocks/Contents/Tabs';
    import Text from '@/Blocks/Contents/Text';
    import UserList from '@/Blocks/Contents/UserList';
    import { isBlank, useModelWrapper, generateElementId } from '@/Libs/utils';
    import { usePage } from '@inertiajs/inertia-vue3';
    import { cloneDeep } from 'lodash';

    export default {
        components: {
            Button,
            Card,
            CardText,
            Carousel,
            Faq,
            Draggable,
            Heading,
            Icon,
            Image,
            Tabs,
            Text,
            UserList,
        },
        props: {
            can: Object,
            id: {},
            isEditMode: {default: false},
            isDebugMode: {default: false},
            components: { type: Array, default: () => [] },
            dataEntities: {},
            dataMedia: {},
            selectedLocale: String,
        },
        emits: [
            'setting-content'
        ],
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
                    this.computedComponents.splice(
                        this.computedComponents.map(block => block.id).indexOf(id),
                        1
                    );

                    delete this.computedDataEntities[id];

                    this.settingContent('');
                }
            },
            duplicateContent(id) {
                if (!isBlank(id)) {
                    const componentId = generateElementId();
                    const duplicateComponent = cloneDeep(
                        this.computedComponents[
                            this.computedComponents.map(block => block.id).indexOf(id)
                        ]
                    );
                    duplicateComponent.id = componentId;

                    const duplicateEntity = cloneDeep(this.computedDataEntities[id]);
                    duplicateEntity.id = componentId;

                    this.computedDataEntities[componentId] = duplicateEntity;
                    this.computedComponents.push(duplicateComponent);
                }
            },
            settingContent(event) {
                this.$emit('setting-content', event)
            },
        }
    }
</script>
