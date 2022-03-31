<template>
    <biz-form-field
        :is-required="required"
    >
        <template
            v-if="label"
            #label
        >
            {{ label }}
        </template>

        <biz-input-color
            ref="input"
            v-bind="$attrs"
            v-model="computedValue"
            :disabled="disabled"
            :has-error="hasError"
            :placeholder="placeholder"
            :required="required"
        />

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizFormField from '@/Biz/Form/Field';
    import BizInputColor from '@/Biz/InputColor';
    import BizInputError from '@/Biz/InputError';
    import MixinHasModal from '@/Mixins/HasModal';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormInputColor',

        components: {
            BizFormField,
            BizInputError,
            BizInputColor,
        },

        mixins: [
            MixinHasModal,
        ],

        inheritAttrs: false,

        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            label: {
                type: String,
                default: null,
            },
            message: {
                type: [Object, null],
                required: true,
            },
            modelValue: {
                type: [String, null],
                default: "",
            },
            placeholder: {
                type: String,
                default: "",
            },
            required: {
                type: Boolean,
                default: false
            },
        },

        emits: [
            'update:modelValue',
        ],

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        computed: {
            hasError() {
                return this.message ? true : false;
            },
        },
    };
</script>
