<template>
    <div>
        <biz-toolbar-content
            @delete-content="$emit('delete-content', id)"
            @duplicate-content="duplicateContent"
        />
        <form-number
            v-model="value"
            :label="modelValue.label"
            :required="modelValue.validation.rules.required"
            :disabled="modelValue.disabled"
            :readonly="modelValue.readonly"
            :placeholder="modelValue.placeholder"
        >
            <template
                v-if="hasNotes"
                #note
            >
                <biz-field-notes
                    type="info"
                    :notes="modelValue.notes"
                />
            </template>
        </form-number>
    </div>
</template>

<script>
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import MixinField from '@formbuilder/Mixins/Field';
    import BizFieldNotes from '@/Biz/FieldNotes.vue';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import FormNumber from '@/Biz/Form/Number.vue';

    export default {
        name: 'InputNumber',

        components: {
            BizFieldNotes,
            BizToolbarContent,
            FormNumber,
        },

        mixins: [
            MixinDuplicableContent,
            MixinField,
        ],

        emits: ['delete-content'],
    };
</script>
