<template>
    <div>
        <biz-toolbar-content
            :can-duplicate="false"
            @delete-content="deleteContent"
        />
        <form-number
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
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import FormNumber from '@/Biz/Form/Number';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'Number',

        components: {
            BizToolbarContent,
            FormNumber,
        },

        mixins: [
            MixinDeletableContent,
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