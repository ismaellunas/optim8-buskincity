<template>
    <div>
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />
        <form-select
            v-model="value"
            class="is-fullwidth"
            :label="entity.label"
            :required="entity.validation.rules.required"
            :disabled="entity.disabled"
            :readonly="entity.readonly"
        >
            <template
                v-for="(option, index) in entity.options"
                :key="index"
            >
                <option :value="option.id">
                    {{ option.value }}
                </option>
            </template>

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
        </form-select>
    </div>
</template>

<script>
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
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