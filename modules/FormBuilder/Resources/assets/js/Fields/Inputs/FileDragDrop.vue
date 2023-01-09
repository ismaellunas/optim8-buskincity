<template>
    <div>
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />
        <form-file-upload
            v-model="value"
            :label="entity.label"
            :required="entity.validation.rules.required"
            :disabled="true"
            :placeholder="entity.placeholder"
        >
            <template
                v-if="entity.note"
                #note
            >
                <p
                    class="help"
                >
                    {{ entity.note }}
                </p>
            </template>
        </form-file-upload>
    </div>
</template>

<script>
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import FormFileUpload from '@/Biz/Form/FileUpload';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FileDragDrop',

        components: {
            BizToolbarContent,
            FormFileUpload,
        },

        mixins: [
            MixinDeletableContent,
            MixinDuplicableContent,
        ],

        props: {
            id: { type: String, required: true },
            modelValue: { type: Object, required: true },
        },

        setup(props, { emit }) {
            return {
                entity: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                value: [],
            };
        },
    }
</script>