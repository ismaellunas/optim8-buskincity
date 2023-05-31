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
        </form-select>
    </div>
</template>

<script>
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import MixinField from '@formbuilder/Mixins/Field';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import FormSelect from '@/Biz/Form/Select.vue';

    export default {
        name: 'InputCountry',

        components: {
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
