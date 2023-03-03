<template>
    <biz-form-field
        :class="fieldClass"
    >
        <template
            v-if="label"
            #label
        >
            {{ label }}
        </template>

        <div
            class="control"
        >
            <biz-text-editor
                v-model="editorValue"
                :disabled="disabled"
                :placeholder="placeholder"
                :config="config ?? editorConfig"
            />
        </div>

        <slot name="note" />

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizFormField from '@/Biz/Form/Field.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizTextEditor from '@/Biz/EditorTinymce.vue';
    import { emailConfig } from '@/Libs/tinymce-configs';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormTextEditor',

        components: {
            BizFormField,
            BizInputError,
            BizTextEditor,
        },

        props: {
            fieldClass: { type: [Object, Array, String], default: undefined },
            modelValue: { type: [String, null], required: true },
            config: { type: Object, default: () => {} },
            disabled: { type: Boolean, default: false },
            height: { type: [Number, String], default: 300 },
            label: { type: String, default: "" },
            message: { type: Object, default: () => {} },
            mode: {
                type: String,
                default: 'default',
                validator(value) {
                    return ['email', 'default'].includes(value);
                },
            },
            placeholder: { type: String, default: "" },
        },

        emits: ['update:modelValue'],

        setup(props, { emit }) {
            return {
                editorValue: useModelWrapper(props, emit),
            };
        },

        computed: {
            editorConfig() {
                const defaultConfig = {
                    inline: false,
                    height: this.height,
                    placeholder: this.placeholder,
                };

                if (this.mode == 'email') {
                    return Object.assign(defaultConfig, emailConfig);
                }

                return defaultConfig;
            },
        },
    };
</script>
