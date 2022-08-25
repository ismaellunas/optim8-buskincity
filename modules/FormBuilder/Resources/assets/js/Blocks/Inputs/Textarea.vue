<template>
    <div>
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />
        <form-textarea
            v-model="value"
            :label="config.properties.label"
            :required="config.validation.required"
            :disabled="config.attributes.disabled"
            :readonly="config.attributes.readonly"
            :placeholder="config.properties.placeholder"
        />
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
                config: props.modelValue.config,
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