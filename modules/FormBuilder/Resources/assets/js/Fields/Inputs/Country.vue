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
            <option :value="null">
                Choose one
            </option>
            <option :value="null">
                English
            </option>
            <option :value="null">
                Sweden
            </option>
            <option :value="null">
                ...
            </option>

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
        name: 'InputCountry',

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
