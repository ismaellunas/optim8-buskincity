<template>
    <biz-field :class="wrapperClass">
        <biz-label
            v-if="label"
            :is-required="required"
        >
            {{ label }}
        </biz-label>

        <biz-field class="has-addons mb-0">
            <p
                class="control"
                :class="{'is-expanded': isExpanded}"
            >
                <biz-number
                    ref="input"
                    v-bind="$attrs"
                    :disabled="disabled"
                    :placeholder="placeholder"
                    :required="required"
                    :value="modelValue"
                    @input="$emit('update:modelValue', $event.target.value)"
                    @keypress="$emit('on-keypress', $event)"
                    @blur="$emit('on-blur', $event)"
                />
            </p>

            <slot name="afterInput" />
        </biz-field>

        <biz-input-error :message="message" />
    </biz-field>
</template>

<script>
    import BizField from '@/Biz/Field';
    import BizNumber from '@/Biz/Number';
    import BizInputError from '@/Biz/InputError';
    import BizLabel from '@/Biz/Label';

    export default {
        name: 'BizFormNumberAddons',

        components: {
            BizField,
            BizNumber,
            BizInputError,
            BizLabel,
        },

        inheritAttrs: false,

        props: {
            disabled: { type: Boolean, default: false },
            label: { type: String, default: '' },
            message: { type: [Array, Object, String], default: undefined },
            modelValue: { type: [String, Number, null], required: true },
            placeholder: { type: String, default: null },
            required: { type: Boolean, default: false },
            wrapperClass: { type: [String, Array, Object], default: () => [] },
            isExpanded: { type: Boolean, default: true },
        },

        emits: [
            'on-blur',
            'on-keypress',
            'update:modelValue',
        ],
    };
</script>
