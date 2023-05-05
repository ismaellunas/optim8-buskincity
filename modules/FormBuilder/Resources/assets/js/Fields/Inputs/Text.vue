<template>
    <div>
        <biz-toolbar-content
            @delete-content="$emit('delete-content', id)"
            @duplicate-content="duplicateContent"
        />
        <form-input
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
        </form-input>
    </div>
</template>

<script>
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import FormInput from '@/Biz/Form/Input.vue';

    export default {
        name: 'InputText',

        components: {
            BizToolbarContent,
            FormInput,
        },

        mixins: [
            MixinDuplicableContent,
        ],


        props: {
            id: { type: String, required: true },
            modelValue: { type: Object, required: true },
        },

        emits: ['delete-content'],

        data() {
            return {
                value: null,
            };
        },
    };
</script>
