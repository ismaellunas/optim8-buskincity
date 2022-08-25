<template>
    <div
        class="columns"
        :class="wrapperClass"
    >
        <div
            class="column is-3 p-1"
        >
            <div class="field has-addons">
                <div class="control is-expanded">
                    <biz-select
                        v-model="numberOfColumns"
                        class="is-fullwidth is-small"
                        @change="onColumnChange"
                    >
                        <option
                            v-for="(columnNumber, index) in columnOptions"
                            :key="index"
                        >
                            {{ columnNumber }}
                        </option>
                    </biz-select>
                </div>
                <div class="control">
                    <biz-button
                        type="button"
                        class="is-static is-small"
                    >
                        Column(s)
                    </biz-button>
                </div>
            </div>
        </div>
        <div
            class="column is-9 p-1"
        >
            <div class="field has-addons is-pulled-right">
                <!-- <p class="control">
                    <biz-button
                        type="button"
                        class="is-small"
                        @click="duplicateBlock"
                    >
                        <span class="icon">
                            <i :class="icon.copy" />
                        </span>
                    </biz-button>
                </p> -->
                <p class="control">
                    <biz-button
                        type="button"
                        class="is-small"
                        @click="deleteBlock"
                    >
                        <span class="icon">
                            <i :class="icon.remove" />
                        </span>
                    </biz-button>
                </p>
                <p class="control">
                    <biz-button
                        type="button"
                        class="is-small handle-columns"
                    >
                        <span class="icon">
                            <i :class="icon.move" />
                        </span>
                    </biz-button>
                </p>
            </div>
        </div>
        <template
            v-for="(column, index) in block.columns"
            :key="column.id"
        >
            <block-column
                :id="column.id"
                :components="block.columns[index].components"
                :data-entities="entities"
            />
        </template>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizSelect from '@/Biz/Select';
    import BlockColumn from './../Blocks/Column';
    import { confirm, confirmDelete } from '@/Libs/alert';
    import { createColumn } from '@/Libs/page-builder.js';
    import { useModelWrapper, isEmpty, getResourceFromDataObject } from '@/Libs/utils';
    import icon from '@/Libs/icon-class';

    export default {
        name: 'Columns',

        components: {
            BizButton,
            BizSelect,
            BlockColumn,
        },

        props: {
            dataEntities: { type: Object, default: () => {} },
            id: { type: String, required: true },
            modelValue: { type: Object, required: true },
        },

        emits: [
            'delete-block',
            'duplicate-block',
        ],

        setup(props, { emit }) {
            return {
                block: useModelWrapper(props, emit),
                entities: useModelWrapper(props, emit, 'dataEntities'),
            };
        },

        data() {
            return {
                columnOptions: [1,2,3,4,5,6],
                icon,
                numberOfColumns: this.block.columns.length,
            };
        },

        computed: {
            wrapperClass() {
                let wrapperClass = [];

                wrapperClass = wrapperClass.concat(
                    'edit-mode-columns',
                    'is-multiline',
                    'box',
                    'p-1',
                    'my-1'
                );

                return wrapperClass.filter(Boolean);
            },
        },

        methods: {
            deleteBlock() {
                const self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.onBlockDeleted();

                        self.$emit('delete-block', self.id)
                    }
                })
            },

            onColumnChange(event) {
                const numberOfColumns = parseInt(event.target.value);
                const originalNumberOfColumns = this.block.columns.length;

                if (numberOfColumns < originalNumberOfColumns) {
                    const confirmText = 'Are you sure you want to decrease the number of column?';

                    confirm(confirmText).then((result) => {
                        if (result.isConfirmed) {
                            const decreaseNumber = originalNumberOfColumns - numberOfColumns;
                            for (let i = 0; i < decreaseNumber; i++) {
                                this.block.columns.pop();
                            }


                        } else {
                            const previousIndex = this.columnOptions.indexOf(originalNumberOfColumns);

                            event.target.selectedIndex = previousIndex;
                            this.numberOfColumns = originalNumberOfColumns;
                            return;
                        }
                    })
                } else {
                    const increaseNumber = numberOfColumns - originalNumberOfColumns;
                    for (let i = 0; i < increaseNumber; i++) {
                        this.block.columns.push(createColumn());
                    }
                }
                this.numberOfColumns = numberOfColumns;
            },

            duplicateBlock() {
                //
            },

            onBlockDuplicated() {
                //
            },

            onBlockDeleted() {
                //
            },
        },
    };
</script>
