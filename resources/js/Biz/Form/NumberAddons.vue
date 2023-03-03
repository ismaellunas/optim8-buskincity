<template>
    <biz-field :class="wrapperClass">
        <biz-label
            v-if="label"
            :class="{'is-size-7': isSmall}"
            :is-required="required"
        >
            {{ label }}
        </biz-label>

        <biz-field class="has-addons mb-0">
            <p
                class="control"
                :class="{'is-expanded': isFullwidth}"
            >
                <biz-number
                    ref="input"
                    v-bind="$attrs"
                    :class="{'is-small': isSmall}"
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

        <slot name="note" />

        <biz-input-error :message="message" />
    </biz-field>
</template>

<script>
    import BizField from '@/Biz/Field.vue';
    import BizNumber from '@/Biz/Number.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizLabel from '@/Biz/Label.vue';

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
            isFullwidth: { type: Boolean, default: true },
            isSmall: { type: Boolean, default: false },
        },

        emits: [
            'on-blur',
            'on-keypress',
            'update:modelValue',
        ],
    };
</script>
