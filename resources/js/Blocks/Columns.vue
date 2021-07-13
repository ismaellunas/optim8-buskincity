<template>
    <div class="columns" :class="wrapperClass">
        <div class="column is-3 p-1" v-if="isEditMode">
            <div class="field has-addons">
                <div class="control is-expanded">
                    <sdb-select
                        class="is-fullwidth is-small"
                        v-model="numberOfColumns"
                        @change="onColumnChange"
                    >
                        <template v-for="columnNumber in columnOptions">
                            <option>{{ columnNumber }}</option>
                        </template>
                    </sdb-select>
                </div>
                <div class="control">
                    <sdb-button type="button" class="is-static is-small">
                        Column(s)
                    </sdb-button>
                </div>
            </div>
        </div>
        <div class="column is-9 p-1" v-if="isEditMode">
            <div class="field has-addons is-pulled-right">
                <p class="control">
                    <sdb-button type="button" class="is-small" @click="deleteBlock">
                        <span class="icon">
                            <i class="fas fa-trash"></i>
                        </span>
                    </sdb-button>
                </p>
                <p class="control">
                    <sdb-button type="button" class="is-small handle-columns">
                        <span class="icon">
                            <i class="fas fa-arrows-alt"></i>
                        </span>
                    </sdb-button>
                </p>
            </div>
        </div>
        <template v-for="(column, index) in block.columns">
            <block-column
                :id="column.id"
                :isEditMode="isEditMode"
                v-model="block.columns[index].components"
            />
        </template>
    </div>
</template>

<script>
    import BlockColumn from '@/Blocks/Column';
    import EditModeComponentMixin from '@/Mixins/EditModeComponent';
    import SdbButton from '@/Sdb/Button';
    import SdbSelect from '@/Sdb/Select';
    import { createColumn } from '@/Libs/page-builder.js';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        mixins: [EditModeComponentMixin],
        components: {
            BlockColumn,
            SdbButton,
            SdbSelect,
        },
        props: {
            id: {},
            isEditMode: {default: false},
            modelValue: {},
        },
        data() {
            return {
                editModeWrapperClass: ['edit-mode-columns'],
                numberOfColumns: this.block.columns.length,
                columnOptions: [1,2,3,4,5,6],
            };
        },
        setup(props, { emit }) {
            return {
                block: useModelWrapper(props, emit),
            };
        },
        methods: {
            deleteBlock() {
                const confirmText = 'Are you sure?';
                if (confirm(confirmText) === true) {
                    this.$emit('delete-block', this.id)
                }
            },
            onColumnChange(event) {
                const numberOfColumns = parseInt(event.target.value);
                const originalNumberOfColumns = this.block.columns.length;

                if (numberOfColumns < originalNumberOfColumns) {
                    const confirmText = 'Are you sure you want to decrease the number of column?';
                    if (confirm(confirmText) === false) {
                        const previousIndex = this.columnOptions.indexOf(originalNumberOfColumns);
                        event.target.selectedIndex = previousIndex;
                        this.numberOfColumns = originalNumberOfColumns;
                        return;
                    }

                    const decreaseNumber = originalNumberOfColumns - numberOfColumns;
                    for (let i = 0; i < decreaseNumber; i++) {
                        this.block.columns.pop();
                    }
                } else {
                    const increaseNumber = numberOfColumns - originalNumberOfColumns;
                    for (let i = 0; i < increaseNumber; i++) {
                        this.block.columns.push(createColumn());
                    }
                }
                this.numberOfColumns = numberOfColumns;
            },
        },
        computed: {
            wrapperClass() {
                let wrapperClass = [];

                wrapperClass = wrapperClass.concat(this.contentClass ?? []);

                if (this.isEditMode) {
                    wrapperClass = wrapperClass.concat(
                        this.editModeWrapperClass,
                        ['is-multiline', 'box', 'p-1', 'my-1']
                    );
                }

                return wrapperClass;
            },
        },
    };
</script>

<style scoped>
.edit-mode-buttons {
    display: none;
    position: absolute;
    bottom: 0.5rem;
    right: 0.5rem;
}
.edit-mode-columns {
    position: relative;
}
.edit-mode-columns:hover > .edit-mode-buttons {
    display: block;
}
</style>
