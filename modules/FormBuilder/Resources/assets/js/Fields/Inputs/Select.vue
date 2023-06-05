<template>
    <div>
        <biz-toolbar-content
            @delete-content="$emit('delete-content', id)"
            @duplicate-content="duplicateContent"
        />
        <form-select
            v-model="value"
            class="is-fullwidth"
            :label="modelValue.label"
            :required="modelValue.validation.rules.required"
            :disabled="modelValue.disabled"
            :readonly="modelValue.readonly"
        >
            <template
                v-for="(option, index) in modelValue.options"
                :key="index"
            >
                <option :value="option.id">
                    {{ option.value }}
                </option>
            </template>

            <template
                v-if="hasNotes"
                #note
            >
                <biz-field-notes
                    type="info"
                    :notes="modelValue.notes"
                />
            </template>
        </form-select>
    </div>
</template>

<script>
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import MixinField from '@formbuilder/Mixins/Field';
    import BizFieldNotes from '@/Biz/FieldNotes.vue';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import FormSelect from '@/Biz/Form/Select.vue';

    export default {
        name: 'InputSelect',

        components: {
            BizFieldNotes,
            BizToolbarContent,
            FormSelect,
        },

        mixins: [
            MixinDuplicableContent,
            MixinField,
        ],

        emits: ['delete-content'],
    };
</script>
