<template>
    <biz-form-field
        :class="fieldClass"
        :is-required="required"
        :label-class="{'is-size-7': isSmall}"
    >
        <template
            v-if="label"
            #label
        >
            {{ label }}
        </template>

        <div class="control">
            <biz-slider
                v-bind="$attrs"
                v-model="computedValue"
                :disabled="disabled"
            />
        </div>

        <slot name="note" />

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizFormField from '@/Biz/Form/Field';
    import BizSlider from '@/Biz/Slider';
    import BizInputError from '@/Biz/InputError';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormSlider',

        components: {
            BizFormField,
            BizSlider,
            BizInputError,
        },

        inheritAttrs: false,

        props: {
            fieldClass: {
                type: [Object, Array, String],
                default: undefined,
            },
            label: {
                type: String,
                default: ''
            },
            message: {
                type: [Array, Object, String],
                default: undefined
            },
            modelValue: {
                type: [Number, String, null],
                required: true
            },
            disabled: {
                type: Boolean,
                default: false
            },
            required: {
                type: Boolean,
                default: false
            },
            isSmall: {
                type: Boolean,
                default: false
            },
        },

        setup(props, {emit}) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },
    };
</script>
