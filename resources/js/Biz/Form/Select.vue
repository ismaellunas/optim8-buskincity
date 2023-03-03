<template>
    <biz-form-field
        :is-required="required"
        :class="fieldClass"
        :label-class="{'is-size-7': isSmall}"
    >
        <template #label>
            {{ label }}
        </template>

        <div :class="inputFieldClass">
            <slot name="beforeInput" />

            <div
                class="control"
                :class="{'is-expanded': isFullwidth}"
            >
                <biz-select
                    v-model="selected"
                    v-bind="$attrs"
                    :disabled="disabled"
                    :placeholder="placeholder"
                    :class="{'is-fullwidth': isFullwidth, 'is-small': isSmall}"
                >
                    <slot />
                </biz-select>
            </div>

            <slot name="afterInput" />
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
    import BizSelect from '@/Biz/Select.vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormSelect',

        components: {
            BizFormField,
            BizInputError,
            BizSelect,
        },

        inheritAttrs: false,

        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            label: {
                type: String,
                default: ""
            },
            message: {
                type: [Array, Object, String],
                default: () => {}
            },
            modelValue: {
                type: [Object, Array, String, Number, Boolean, null],
                required: true
            },
            placeholder: {
                type: String,
                default: ""
            },
            required: {
                type: Boolean,
                default: false
            },
            fieldClass: {
                type: [Object, Array, String],
                default: undefined,
            },
            hasAddons: {
                type: Boolean,
                default: false
            },
            isFullwidth: {
                type: Boolean,
                default: false
            },
            isSmall: {
                type: Boolean,
                default: false
            },
        },

        setup(props, { emit }) {
            return {
                selected: useModelWrapper(props, emit),
            };
        },

        computed: {
            inputFieldClass() {
                const classes = {field: false};

                if (this.hasAddons) {
                    classes['field'] = true;
                    classes['has-addons'] = true;
                }

                return classes;
            },
        },
    };
</script>
