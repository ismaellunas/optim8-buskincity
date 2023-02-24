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
            <div class="control is-expanded">
                <biz-input
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
            </div>

            <slot name="afterInput" />
        </biz-field>

        <slot name="note" />

        <biz-input-error :message="message" />
    </biz-field>
</template>

<script>
    import BizField from '@/Biz/Field.vue';
    import BizInput from '@/Biz/Input.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizLabel from '@/Biz/Label.vue';

    export default {
        name: 'BizFormInputAddons',
        components: {
            BizField,
            BizInput,
            BizInputError,
            BizLabel,
        },
        inheritAttrs: false,
        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            label: String,
            message: { type: [String, Array], default: undefined },
            modelValue: {},
            placeholder: String,
            required: {
                type: Boolean,
                default: false
            },
            wrapperClass: {
                type: [String, Array, Object],
                default: () => [],
            },
            isSmall: {
                type: Boolean,
                default: false
            },
        },
        emits: [
            'on-blur',
            'on-keypress',
            'update:modelValue',
        ],
    };
</script>
