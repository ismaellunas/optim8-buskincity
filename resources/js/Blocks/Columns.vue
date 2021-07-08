<template>
    <div class="columns" :class="wrapperClass">
        <template v-for="(column, index) in block.columns">
            <block-column
                :id="column.id"
                v-model="block.columns[index].components"
                :isEditMode="isEditMode"
            />
        </template>
        <div class="edit-mode-buttons" v-if="isEditMode">
            <button class="button is-small" type="button" @click="deleteBlock">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</template>

<script>
    import BlockColumn from '@/Blocks/Column';
    import EditModeComponentMixin from '@/Mixins/EditModeComponent';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        mixins: [EditModeComponentMixin],
        components: {
            BlockColumn,
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
        }
    };
</script>

<style scoped>
.edit-mode-buttons {
    /*opacity: 0;*/
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
