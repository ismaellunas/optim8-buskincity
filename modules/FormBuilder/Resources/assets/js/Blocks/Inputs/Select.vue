<template>
    <div>
        <biz-toolbar-content
            :can-duplicate="false"
            @delete-content="deleteContent"
        />
        <form-select
            v-model="value"
            class="is-fullwidth"
            :label="config.properties.label"
            :required="config.validation.required"
            :disabled="config.attributes.disabled"
        >
            <template
                v-for="(option, index) in config.data.options"
                :key="index"
            >
                <option :value="option.id">
                    {{ option.value }}
                </option>
            </template>
        </form-select>
    </div>
</template>

<script>
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import FormSelect from '@/Biz/Form/Select';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'Select',

        components: {
            BizToolbarContent,
            FormSelect,
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