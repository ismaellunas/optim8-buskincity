<template>
    <template v-if="isEditMode">
        <h2 class="title" :class="wrapperClass">
            <sdb-ckeditor-inline v-model="content.h2.html"/>

            <div class="edit-mode-buttons">
                <button class="button is-small" type="button" @click="deleteContent">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </button>
            </div>
        </h2>
    </template>
    <template v-else>
        <h2 class="title" :class="wrapperClass" v-html="content.h2.html"></h2>
    </template>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeContentMixin from '@/Mixins/EditModeContent';
    import SdbCkeditorInline from '@/Sdb/CkeditorInline';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        mixins: [
            EditModeContentMixin,
            DeletableContentMixin
        ],
        components: {
            SdbCkeditorInline,
        },
        props: {
            class: {type: Array},
            id: {},
            modelValue: {},
        },
        setup(props, { emit }) {
            const content = props.modelValue;
            return {
                content: useModelWrapper(props, emit),
                contentClass: content.h2.attrs.class ?? [],
            };
        },
    }
</script>

<style scoped>
.edit-mode-content {
    border: 1px dotted;
}
.edit-mode-buttons {
    line-height: 0.5;
}
</style>
<style scoped src="../../../css/column-content.css"></style>
