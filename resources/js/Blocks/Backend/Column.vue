<template>
    <div class="column" :class="columnClass">
        <draggable
            v-if="isEditMode"
            class="dragArea list-group"
            group="components"
            handle=".handle-content"
            item-key="id"
            :animation="300"
            :empty-insert-threshold="emptyInsertThreshold"
            :list="components"
            @change="log"
        >
            <template #item="{ element }">
                <component
                    :is="element.componentName"
                    :id="element.id"
                    v-model="dataEntities[element.id]"
                    class="page-component"
                    :can="can"
                    :is-edit-mode="isEditMode"
                    :data-images="dataImages"
                    :data-media="dataMedia"
                    :selected-locale="selectedLocale"
                    @click="settingContent(element.id)"
                    @delete-content="deleteContent"
                />
            </template>
        </draggable>

        <template v-else>
            <component
                :is="element.componentName"
                v-for="element in components"
                :key="element.id"
                :id="element.id"
                v-model="dataEntities[element.id]"
                :data-media="dataMedia"
                :is-edit-mode="isEditMode"
                :selected-locale="selectedLocale"
            />
        </template>
    </div>
</template>

<script>
    import Card from '@/Blocks/Backend/Contents/Card';
    import CardText from '@/Blocks/Backend/Contents/CardText';
    import Draggable from 'vuedraggable';
    import Heading from '@/Blocks/Backend/Contents/Heading';
    import Image from '@/Blocks/Backend/Contents/Image';
    import Text from '@/Blocks/Backend/Contents/Text';
    import { isBlank } from '@/Libs/utils';
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
            can: Object,
            id: {},
            isEditMode: {default: false},
            isDebugMode: {default: false},
            components: {type: Array, default: []},
            dataEntities: {},
            dataImages: {type: Object, default: {}},
            dataMedia: {},
            selectedLocale: String,
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
