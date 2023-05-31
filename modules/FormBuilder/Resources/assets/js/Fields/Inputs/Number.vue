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
                <p
                    class="help is-info"
                >
                    <ul>
                        <li
                            v-for="(note, index) in modelValue.notes"
                            :key="index"
                        >
                            {{ note }}
                        </li>
                    </ul>
                </p>
            </template>
        </form-number>
    </div>
</template>

<script>
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import MixinField from '@formbuilder/Mixins/Field';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import FormNumber from '@/Biz/Form/Number.vue';

    export default {
        name: 'InputNumber',

        components: {
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
