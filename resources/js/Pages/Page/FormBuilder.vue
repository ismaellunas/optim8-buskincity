<template>
    <div class="columns">
        <div class="column is-3 is-narrow">
            <draggable
                class="dragArea columns is-multiline"
                :list="availableComponents"
                :group="{ name: 'components', pull: 'clone', put: false }"
                :clone="cloneComponent"
                :sort="false"
                @change="log"
                item-key="component"
            >
                <template #item="{ element }">
                    <div class="column is-half">
                        <div class="card">
                            <div class="card-content">
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
                :list="availableBlocks"
                :group="{ name: 'columns', pull: 'clone', put: false }"
                :clone="cloneBlock"
                :sort="false"
                @change="log"
                item-key="name"
            >
                <template #item="{ element }">
                    <div class="column is-half">
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
        </div>

        <div class="column is-9">
            <draggable
                :sort="true"
                animation="300"
                class="list-block-columns"
                group="columns"
                handle=".handle-columns"
                item-key="id"
                v-model="data"
            >
                <template #item="{element, index}">
                    <block-columns
                        v-model="data[index]"
                        :isEditMode="isEditMode"
                        :id="element.id"
                        @delete-block="deleteBlock"
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
    import { generateElementId, useModelWrapper } from '@/Libs/utils'
    import { createBlock, createColumn } from '@/Libs/page-builder.js';

    export default {
        components: {
            BlockColumns,
            Draggable,
        },
        props: {
            errors: Object,
            isEditMode: {type: Boolean, default: false},
            modelValue: Array,
        },
        setup(props, { emit }) {
            return {
                data: useModelWrapper(props, emit),
            };
        },
        methods: {
            log: function(evt) {
                window.console.log(evt);
            },
            cloneBlock(seletectedBlock) {
                const clonedBlock = JSON.parse(JSON.stringify(seletectedBlock));
                clonedBlock.id = generateElementId();
                delete clonedBlock.title;

                return clonedBlock;
            },
            cloneComponent(seletectedComponent) {
                const clonedContent = JSON.parse(JSON.stringify(seletectedComponent));
                clonedContent.id = generateElementId();
                return clonedContent;
            },
            deleteBlock(id) {
                const removeIndex = this.data.map(block => block.id).indexOf(id);
                this.data.splice(removeIndex, 1);
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
            }
        }
    }
</script>
