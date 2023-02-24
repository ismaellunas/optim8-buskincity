<template>
    <div>
        <biz-toolbar-content
            @delete-content="deleteContent"
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
                v-if="modelValue.note"
                #note
            >
                <p
                    class="help"
                >
                    {{ modelValue.note }}
                </p>
            </template>
        </form-number>
    </div>
</template>

<script>
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import FormNumber from '@/Biz/Form/Number.vue';

    export default {
        name: 'InputNumber',

        components: {
            BizToolbarContent,
            FormNumber,
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