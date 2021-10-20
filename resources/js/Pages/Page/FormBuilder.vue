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
                        @end="onEnd"
                        @change="log"
                        item-key="id"
                    >
                        <template #item="{ element }">
                            <div class="column is-half">
                                <div class="card" :class="{'has-text-grey-light': !isEditMode}">
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
                        @change="log"
                        item-key="name"
                    >
                        <template #item="{ element }">
                            <div class="column is-half">
                                <div class="card" :class="{'has-text-grey-light': !isEditMode}">
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
                    <sdb-component-config
                        v-model="data.entities[contentConfigId]"
                        class="page-builder-content-config"
                    />
                </template>
            </div>
        </div>

        <div class="column is-9" :class="{'has-background-grey-lighter has-text-centered': !hasBlok}">
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
                        :can="can"
                        :isEditMode="isEditMode"
                        :selected-locale="selectedLocale"
                        :data-images="dataImages"
                        @delete-block="deleteBlock"
                        @setting-content="settingContent"
                        />
                </template>
            </draggable>
        </div>
    </div>
</template>

<script>
    import BlockColumns from '@/Blocks/Backend/Columns'
    import ComponentStructures from '@/ComponentStructures';
    import Draggable from "vuedraggable";
    import SdbComponentConfig from '@/Sdb/ComponentConfig';
    import { isBlank, generateElementId, useModelWrapper } from '@/Libs/utils'
    import { createBlock, createColumn } from '@/Libs/page-builder.js';

    export default {
        components: {
            BlockColumns,
            Draggable,
            SdbComponentConfig,
        },
        props: {
            can: Object,
            errors: Object,
            isEditMode: {type: Boolean, default: false},
            modelValue: {type: Object},
            contentConfigId: {},
            selectedLocale: String,
            dataImages: {type: Object, default: {}},
        },
        setup(props, { emit }) {
            return {
                data: useModelWrapper(props, emit),
                contentConfigId: useModelWrapper(props, emit, 'contentConfigId'),
            };
        },
        created() {
            // NOTE fix page.data
            if (!this.data?.media) {
                this.data.media = [];
            }
        },
        data() {
            return {
                isDebugMode: false,
                clonedComponent: null,
            };
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
            },
            settingContent(id) {
                this.contentConfigId = id;
            }
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
                    let block = createBlock();
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
        }
    }
</script>
