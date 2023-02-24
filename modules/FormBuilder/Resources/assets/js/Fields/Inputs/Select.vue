<template>
    <div>
        <biz-toolbar-content
            @delete-content="deleteContent"
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
                v-if="modelValue.note"
                #note
            >
                <p
                    class="help"
                >
                    {{ modelValue.note }}
                </p>
            </template>
        </form-select>
    </div>
</template>

<script>
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import FormSelect from '@/Biz/Form/Select.vue';

    export default {
        name: 'InputSelect',

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

        data() {
            return {
                value: null,
            };
        },
    };
</script>