<template>
    <div class="columns">
        <div class="column is-3 is-narrow">
            <div
                id="side-menu-page-builder"
                class="p-1"
            >
                <template v-if="!isComponentConfigOpen">
                    <biz-card
                        ref="component-0"
                        class="mb-1"
                        :is-collapsed="true"
                        :is-expanding-on-load="true"
                        @on-click-header-card="onClickHeaderCard($event, 0)"
                    >
                        <template #headerTitle>
                            General
                        </template>

                        <draggable
                            class="dragArea columns is-multiline"
                            :list="availableComponents"
                            :group="{ name: 'components', pull: 'clone', put: false }"
                            :clone="cloneComponent"
                            :sort="false"
                            item-key="id"
                            @end="onEnd"
                            @change="log"
                        >
                            <template #item="{ element }">
                                <div class="column is-half p-2">
                                    <div class="card">
                                        <div class="card-content is-size-7">
                                            <div class="content is-center">
                                                {{ element.title }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </draggable>
                    </biz-card>

                    <biz-card
                        v-if="hasModuleComponent"
                        ref="component-1"
                        class="mb-1"
                        :is-collapsed="true"
                        @on-click-header-card="onClickHeaderCard($event, 1)"
                    >
                        <template #headerTitle>
                            Modules
                        </template>

                        <draggable
                            class="dragArea columns is-multiline"
                            :list="availableModuleComponents"
                            :group="{ name: 'components', pull: 'clone', put: false }"
                            :clone="cloneComponent"
                            :sort="false"
                            item-key="id"
                            @end="onEnd"
                            @change="log"
                        >
                            <template #item="{ element }">
                                <div class="column is-half p-2">
                                    <div class="card">
                                        <div class="card-content is-size-7">
                                            <div class="content is-center">
                                                {{ element.title }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </draggable>
                    </biz-card>

                    <biz-card
                        ref="component-2"
                        class="mb-1"
                        :is-collapsed="true"
                        @on-click-header-card="onClickHeaderCard($event, 2)"
                    >
                        <template #headerTitle>
                            Columns
                        </template>

                        <draggable
                            class="dragColumnArea columns is-multiline"
                            :list="availableBlocks"
                            :group="{ name: 'columns', pull: 'clone', put: false }"
                            :clone="cloneBlock"
                            :sort="false"
                            item-key="name"
                            @change="log"
                        >
                            <template #item="{ element }">
                                <div class="column is-half p-2">
                                    <div class="card">
                                        <div class="card-content is-size-7">
                                            <div class="content">
                                                {{ element.title }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </draggable>
                    </biz-card>
                </template>

                <template v-else>
                    <biz-component-config
                        v-model="data.entities[contentConfigId]"
                        :structure="dataStructure"
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
                        class="component-configurable"
                        :data-id="element.id"
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
    import BizCard from '@/Biz/Card.vue';
    import BizComponentConfig from '@/Biz/ComponentConfig.vue';
    import BlockColumns from '@/Blocks/Columns.vue';
    import ModuleComponentStructures from '@/Modules/ComponentStructures';
    import ComponentStructures from '@/ComponentStructures';
    import blockColumns from '@/ComponentStructures/columns';
    import Draggable from "vuedraggable";
    import { cloneDeep } from 'lodash';
    import { createColumn } from '@/Libs/page-builder.js';
    import { isModuleActive } from '@/Libs/module';
    import { pascalCase } from 'change-case';
    import {
        isBlank,
        generateElementId,
        useModelWrapper,
        getResourceFromDataObject
    } from '@/Libs/utils';

    export default {
        name: 'PageFormBuilder',

        components: {
            BizCard,
            BizComponentConfig,
            BlockColumns,
            Draggable,
        },

        provide() {
            return {
                dataMedia: this.data.media,
                selectedLocale: this.selectedLocale,
            };
        },

        props: {
            contentConfigId: { type: String, default: "" },
            modelValue: { type: Object, required: true },
            selectedLocale: { type: String, required: true },
        },

        emits: [
            'update:contentConfigId',
        ],

        setup(props, { emit }) {
            return {
                computedContentConfigId: useModelWrapper(props, emit, 'contentConfigId'),
                data: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                clonedComponent: null,
                isDebugMode: false,
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
            availableModuleComponents() {
                let components = [];

                for (const property in ModuleComponentStructures) {
                    if (isModuleActive(pascalCase(property))) {
                        components.push(ModuleComponentStructures[property]);
                    }
                }

                return components;
            },
            availableBlocks() {
                const maxColumnNumber = 6;
                let blocks = [];

                for (let i = 1; i <= maxColumnNumber; i++) {
                    let block = JSON.parse(JSON.stringify(blockColumns));

                    for (let colIndex = 1; colIndex <= i; colIndex++) {
                        block.columns.push(createColumn());
                    }
                    block.id = '';
                    block.title = i+' Column'+((i>1) ? 's' : '');

                    let configColumns = [];

                    for (let columnIndex = 1; columnIndex <= i; columnIndex++) {
                        configColumns.push(cloneDeep(block.config.columns.column[0]));
                    }

                    block.config.columns.column = configColumns;
                    blocks.push(block);
                }

                return blocks;
            },
            hasBlok() {
                return isBlank(this.data.structures) ? false : this.data.structures.length > 0;
            },
            hasModuleComponent() {
                return this.availableModuleComponents.length > 0;
            },
            isComponentConfigOpen() {
                return (
                    !isBlank(this.contentConfigId)
                    && this.data.entities[this.contentConfigId]
                );
            },
            dataStructure() {
                let index = this.data.structures.findIndex(
                    structures => structures.id == this.contentConfigId
                );

                return this.data.structures[index] ?? {};
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

                let removeIds = getResourceFromDataObject(
                    this.data.structures[removeIndex],
                    'id'
                );

                this.data.structures.splice(removeIndex, 1);

                for (let i = 0; i < removeIds.length; i++) {
                    delete this.data.entities[removeIds[i]];
                }
            },
            settingContent(event) {
                const configComponent = event.target.closest('.component-configurable');

                if (configComponent) {
                    if (configComponent.hasAttribute('data-id')) {
                        this.computedContentConfigId = configComponent.getAttribute('data-id');
                    }
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

                        this.duplicateEntity(component.id, componentId);

                        component.id = componentId;

                        return component;
                    });

                    return column;
                });

                this.data.structures.splice( (duplicateIndex + 1), 0, duplicateBlock );

                this.duplicateEntity(id, duplicateBlock.id);
            },
            duplicateEntity(oldId, newId) {
                const duplicateEntity = cloneDeep(this.data.entities[oldId]);
                duplicateEntity.id = newId;

                this.data.entities[newId] = duplicateEntity;
            },
            onClickHeaderCard(isContentShown, index) {
                if (isContentShown) {
                    for (let i = 0; i < 3; i++) {
                        if (i != index) {
                            this.$refs[`component-${i}`].isContentShown = false;
                        }
                    }
                }
            },
        },
    }
</script>
