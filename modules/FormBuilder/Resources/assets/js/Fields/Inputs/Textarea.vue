<template>
    <div>
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />
        <form-textarea
            v-model="value"
            :label="entity.label"
            :required="entity.validation.rules.required"
            :disabled="entity.disabled"
            :readonly="entity.readonly"
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
        </form-textarea>
    </div>
</template>

<script>
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import FormTextarea from '@/Biz/Form/Textarea';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'Textarea',

        components: {
            BizToolbarContent,
            FormTextarea,
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
                value: null,
            };
        },
    };
</script>