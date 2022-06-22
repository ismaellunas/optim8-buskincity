<template>
    <div class="columns">
        <div class="column is-3 is-narrow">
            <div id="side-menu-page-builder">
                <template v-if="!isComponentConfigOpen">
                    <draggable
                        class="dragArea columns is-multiline"
                        :disabled="!isEditMode"
                        :list="availableComponents"
                        :group="{ name: 'components', pull: 'clone', put: false }"
                        :clone="cloneComponent"
                        :sort="false"
                        item-key="id"
                        @end="onEnd"
                        @change="log"
                    >
                        <template #item="{ element }">
                            <div class="column is-half">
                                <div
                                    class="card"
                                    :class="{'has-text-grey-light': !isEditMode}"
                                >
                                    <div class="card-content is-size-7">
                                        <div class="content is-center">
                                            {{ element.title }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </draggable>

                    <draggable
                        class="dragColumnArea columns is-multiline"
                        :disabled="!isEditMode"
                        :list="availableBlocks"
                        :group="{ name: 'columns', pull: 'clone', put: false }"
                        :clone="cloneBlock"
                        :sort="false"
                        item-key="name"
                        @change="log"
                    >
                        <template #item="{ element }">
                            <div class="column is-half">
                                <div
                                    class="card"
                                    :class="{'has-text-grey-light': !isEditMode}"
                                >
                                    <div class="card-content is-size-7">
                                        <div class="content">
                                            {{ element.title }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </draggable>
                </template>

                <template v-else>
                    <biz-component-config
                        v-model="data.entities[contentConfigId]"
                        class="page-builder-content-config"
                    />
                </template>
            </div>
        </div>

        <div
            class="column is-9"
            :class="{'has-background-grey-lighter has-text-centered': !hasBlok}"
        >
            <draggable
                class="list-block-columns"
                group="columns"
                handle=".handle-columns"
                item-key="id"
                :animation="300"
                :empty-insert-threshold="5"
                :list="data.structures"
                :scroll-sensitivity="200"
                :sort="true"
            >
                <template #item="{element, index}">
                    <block-columns
                        :id="element.id"
                        v-model="data.structures[index]"
                        v-model:data-entities="data.entities"
                        v-model:data-media="data.media"
                        class="component-configurable"
                        :can="can"
                        :data-id="element.id"
                        :is-edit-mode="isEditMode"
                        :selected-locale="selectedLocale"
                        @click="settingContent"
                        @delete-block="deleteBlock"
                        @duplicate-block="duplicateBlock"
                        @setting-content="settingContent"
                    />
                </template>
            </draggable>
        </div>
    </div>
</template>

<script>
    import BlockColumns from '@/Blocks/Columns'
    import ComponentStructures from '@/ComponentStructures';
    import Draggable from "vuedraggable";
    import BizComponentConfig from '@/Biz/ComponentConfig';
    import { isBlank, generateElementId, useModelWrapper } from '@/Libs/utils'
    import { createColumn } from '@/Libs/page-builder.js';
    import { cloneDeep } from 'lodash';

    export default {
        components: {
            BlockColumns,
            Draggable,
            BizComponentConfig,
        },

        props: {
            can: Object,
            contentConfigId: { type: String, default: "" },
            errors: Object,
            isEditMode: {type: Boolean, default: false},
            modelValue: {type: Object},
            selectedLocale: String,
        },

        emits: [
            'update:contentConfigId',
        ],

        setup(props, { emit }) {
            return {
                data: useModelWrapper(props, emit),
                computedContentConfigId: useModelWrapper(props, emit, 'contentConfigId'),
            };
        },

        data() {
            return {
                isDebugMode: false,
                clonedComponent: null,
            };
        },
        computed: {
            availableComponents() {
                let components = [];
                for (const property in ComponentStructures) {
                    components.push(ComponentStructures[property]);
                }
                return components;
            },
            availableBlocks() {
                const maxColumnNumber = 6;
                let blocks = [];

                for (let i = 1; i <= maxColumnNumber; i++) {
                    let block = JSON.parse(JSON.stringify(ComponentStructures.columns));

                    for (let colIndex = 1; colIndex <= i; colIndex++) {
                        block.columns.push(createColumn());
                    }
                    block.id = '';
                    block.title = i+' Column'+((i>1) ? 's' : '');
                    blocks.push(block);
                }

                return blocks;
            },
            hasBlok() {
                return isBlank(this.data.structures) ? false : this.data.structures.length > 0;
            },
            isComponentConfigOpen() {
                return (
                    !isBlank(this.contentConfigId)
                    && this.data.entities[this.contentConfigId]
                );
            },
        },
        created() {
            // NOTE fix page.data
            if (!this.data?.media) {
                this.data.media = [];
            }
        },
        methods: {
            log: function(evt) {
                if (this.isDebugMode) {
                    console.log(evt);
                }
            },
            isComponentCloned(evt) {
                return (evt.pullMode === "clone");
            },
            onEnd(evt) {
                const component = this.clonedComponent;

                if (!this.isComponentCloned(evt)) {
                    delete this.data.entities[component.id];
                }
            },
            cloneBlock(seletectedBlock) {
                const clonedBlock = JSON.parse(JSON.stringify(seletectedBlock));
                clonedBlock.id = generateElementId();

                delete clonedBlock.title;

                const columnEntity = JSON.parse(JSON.stringify(clonedBlock));

                delete columnEntity.columns;

                this.data.entities[clonedBlock.id] = columnEntity;

                delete clonedBlock.config;

                return clonedBlock;
            },
            cloneComponent(seletectedComponent) {
                this.clonedComponent = JSON.parse(JSON.stringify(seletectedComponent));
                this.clonedComponent.id = generateElementId();

                this.data.entities[this.clonedComponent.id] = this.clonedComponent;

                return {
                    id: this.clonedComponent.id,
                    componentName: this.clonedComponent.componentName,
                };
            },
            deleteBlock(id) {
                const removeIndex = this.data.structures.map(block => block.id).indexOf(id);
                this.data.structures.splice(removeIndex, 1);

                delete this.data.entities[id];
            },
            settingContent(event) {
                const configComponent = event.target.closest('.component-configurable');

                if (configComponent.hasAttribute('data-id')) {
                    this.computedContentConfigId = configComponent.getAttribute('data-id');
                }
            },
            duplicateBlock(id) {
                const duplicateIndex = this.data.structures.map(block => block.id).indexOf(id);
                const duplicateBlock = cloneDeep(this.data.structures[duplicateIndex]);

                duplicateBlock.id = generateElementId();
                duplicateBlock.columns.map(column => {
                    column.id = generateElementId();

                    column.components.map(component => {
                        const componentId = generateElementId();
                        const duplicateComponent = cloneDeep(
                            this.data.entities[component.id]
                        );

                        component.id = componentId;
                        duplicateComponent.id = componentId;

                        this.data.entities[componentId] = duplicateComponent;

                        return component;
                    });

                    return column;
                });

                this.data.structures.splice( (duplicateIndex + 1), 0, duplicateBlock );
            },
        },
    }
</script>
