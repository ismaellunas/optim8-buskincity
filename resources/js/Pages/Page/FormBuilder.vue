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
        </div>

        <div class="column is-9">
            <template v-for="(block, index) in data" :key="block.id">
                <block-columns
                    v-model="data[index]"
                    :isEditMode="isEditMode"
                    :id="block.id"
                    @delete-block="deleteBlock"
                    />
            </template>

            <div class="box columns is-multiline is-centered mt-3">
                <div class="column is-2">
                    <button type="button" class="button" @click="addColumnsBlock(1)">1 column</button>
                </div>
                <div class="column is-2">
                    <button type="button" class="button" @click="addColumnsBlock(2)">2 columns</button>
                </div>
                <div class="column is-2">
                    <button type="button" class="button" @click="addColumnsBlock(3)">3 columns</button>
                </div>
                <div class="column is-2">
                    <button type="button" class="button" @click="addColumnsBlock(4)">4 columns</button>
                </div>
                <div class="column is-2">
                    <button type="button" class="button" @click="addColumnsBlock(5)">5 columns</button>
                </div>
                <div class="column is-2">
                    <button type="button" class="button" @click="addColumnsBlock(6)">6 columns</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import BlockColumns from '@/Blocks/Columns'
    import ComponentStructures from '@/ComponentStructures';
    import Draggable from "vuedraggable";
    import { generateElementId, useModelWrapper } from '@/Libs/utils'

    export default {
        components: {
            BlockColumns,
            Draggable,
        },
        props: {
            errors: Object,
            isEditMode: {type: Boolean, default: false},
            modelValue: {type: Array, default: []},
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
            cloneComponent(seletectedComponent) {
                const clonedContent = JSON.parse(JSON.stringify(seletectedComponent));
                clonedContent.id = generateElementId();
                return clonedContent;
            },
            createColumn() {
                return {
                    id: generateElementId(),
                    components: [],
                };
            },
            addColumnsBlock(columnNumber) {
                let block = {
                    id: generateElementId(),
                    type: 'columns',
                    columns: [],
                };

                for (let i = 0; i < columnNumber; i++) {
                    block.columns.push(this.createColumn());
                }

                this.data.push(block);
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
            }
        }
    }
</script>
