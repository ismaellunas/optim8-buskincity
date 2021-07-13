<template>
    <div class="columns" :class="wrapperClass">
        <div class="column is-3 p-1" v-if="isEditMode">
            <div class="field has-addons">
                <div class="control is-expanded">
                    <div class="select is-fullwidth is-small">
                        <sdb-select v-model="block.numberOfColumns">
                            <option>1</option>
                            <option>2</option>
                            <option>3</option>
                            <option>4</option>
                            <option>5</option>
                            <option>6</option>
                        </sdb-select>
                    </div>
                </div>
                <div class="control">
                    <button type="submit" class="button is-static is-small">Column(s)</button>
                </div>
            </div>
        </div>
        <div class="column is-9 p-1" v-if="isEditMode">
            <div class="field has-addons is-pulled-right">
                <p class="control">
                    <sdb-button type="button" class="is-small">
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
            };
        },
        setup(props, { emit }) {
            return {
                block: useModelWrapper(props, emit),
            };
        },
        methods: {
            deleteBlock() {
                this.$emit('delete-block', this.id)
            }
        },
        computed: {
            wrapperClass() {
                let wrapperClass = EditModeComponentMixin.computed.wrapperClass();
                if (this.isEditMode) {
                    wrapperClass.push('is-multiline', 'box', 'p-1', 'my-1');
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
