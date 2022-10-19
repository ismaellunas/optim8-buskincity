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
                    class="component-configurable"
                    :data-id="element.id"
                    :is-edit-mode="isEditMode"
                    :selected-locale="selectedLocale"
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
    import FormBuilder from '@mod/FormBuilder/Resources/assets/js/Blocks/Contents/FormBuilder';
    import Heading from '@/Blocks/Contents/Heading';
    import Icon from '@/Blocks/Contents/Icon';
    import Image from '@/Blocks/Contents/Image';
    import LatestPost from '@/Blocks/Contents/LatestPost';
    import Tabs from '@/Blocks/Contents/Tabs';
    import Text from '@/Blocks/Contents/Text';
    import UserList from '@/Blocks/Contents/UserList';
    import Video from '@/Blocks/Contents/Video';
    import { isBlank, useModelWrapper, generateElementId } from '@/Libs/utils';
    import { usePage } from '@inertiajs/inertia-vue3';
    import { cloneDeep } from 'lodash';

    export default {
        components: {
            Button,
            Card,
            CardText,
            Carousel,
            Draggable,
            Faq,
            FormBuilder,
            Heading,
            Icon,
            Image,
            LatestPost,
            Tabs,
            Text,
            UserList,
            Video,
        },
        props: {
            id: { type: String, required: true },
            isEditMode: { type: Boolean, default: false },
            isDebugMode: { type: Boolean, default: false },
            components: { type: Array, default: () => [] },
            dataEntities: { type: Object, default: () => {} },
            selectedLocale: { type: String, required: true },
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
