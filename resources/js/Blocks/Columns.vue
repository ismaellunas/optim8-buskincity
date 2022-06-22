<template>
    <div
        class="columns"
        :class="wrapperClass"
        :style="wrapperStyle"
    >
        <div
            v-if="isEditMode"
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
            v-if="isEditMode"
            class="column is-9 p-1"
        >
            <div class="field has-addons is-pulled-right">
                <p class="control">
                    <biz-button
                        type="button"
                        class="is-small"
                        @click="duplicateBlock"
                    >
                        <span class="icon">
                            <i class="fas fa-copy" />
                        </span>
                    </biz-button>
                </p>
                <p class="control">
                    <biz-button
                        type="button"
                        class="is-small"
                        @click="deleteBlock"
                    >
                        <span class="icon">
                            <i class="fas fa-trash" />
                        </span>
                    </biz-button>
                </p>
                <p class="control">
                    <biz-button
                        type="button"
                        class="is-small handle-columns"
                    >
                        <span class="icon">
                            <i class="fas fa-arrows-alt" />
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
                :can="can"
                :components="block.columns[index].components"
                :data-entities="entities"
                :data-media="media"
                :is-edit-mode="isEditMode"
                :selected-locale="selectedLocale"
            />
        </template>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizSelect from '@/Biz/Select';
    import BlockColumn from '@/Blocks/Column';
    import EditModeComponentMixin from '@/Mixins/EditModeComponent';
    import { confirm, confirmDelete } from '@/Libs/alert';
    import { createColumn } from '@/Libs/page-builder.js';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizButton,
            BizSelect,
            BlockColumn,
        },

        mixins: [
            EditModeComponentMixin
        ],

        props: {
            can: { type: Object, required: true },
            dataEntities: { type: Object, default: () => {} },
            dataMedia: { type: Array, default: () => [] },
            id: { type: String, required: true },
            isEditMode: { type: Boolean, default: false },
            modelValue: { type: Object, required: true },
            selectedLocale: { type: String, required: true },
        },

        emits: [
            'delete-block',
            'duplicate-block',
        ],

        setup(props, { emit }) {
            return {
                block: useModelWrapper(props, emit),
                entities: useModelWrapper(props, emit, 'dataEntities'),
                media: useModelWrapper(props, emit, 'dataMedia'),
            };
        },

        data() {
            return {
                columnOptions: [1,2,3,4,5,6],
                editModeWrapperClass: ['edit-mode-columns'],
                numberOfColumns: this.block.columns.length,
            };
        },

        computed: {
            wrapperClass() {
                let wrapperClass = [];

                if (this.isEditMode) {
                    wrapperClass = wrapperClass.concat(
                        'edit-mode-columns', 'is-multiline', 'box', 'p-1', 'my-1'
                    );
                }

                return wrapperClass;
            },

            dataEntity() {
                return this.dataEntities[ this.block.id ] ?? null;
            },

            wrapperStyle() {
                const styles = {};

                const configWrapper = this.dataEntity?.config?.wrapper ?? null;

                if (configWrapper && configWrapper["style.margin"]) {
                    const styleMargin = configWrapper["style.margin"];

                    for (const [key, margin] of Object.entries(styleMargin)) {
                        if (! (styleMargin[key] == null || styleMargin[key] == "")) {
                            styles['margin-'+key] = styleMargin[key]+'px !important';
                        }
                    }
                }

                if (configWrapper && configWrapper["style.padding"]) {
                    const stylePadding = configWrapper["style.padding"];

                    for (const [key, padding] of Object.entries(stylePadding)) {
                        if (! (stylePadding[key] == null || stylePadding[key] == "")) {
                            styles['padding-'+key] = stylePadding[key]+'px !important';
                        }
                    }
                }

                return styles;
            },
        },

        methods: {
            deleteBlock() {
                const self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.$emit('delete-block', self.id)
                    }
                })
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

            duplicateBlock() {
                const self = this;

                confirm(
                    'Duplicate Component?'
                ).then((result) => {
                    if (result.isConfirmed) {
                        self.$emit('duplicate-block', self.id)
                    }
                });
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
