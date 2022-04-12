<template>
    <biz-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <template v-if="layout == 'horizontal'">
            <div class="control">
                <biz-radio
                    v-for="(option, index) in options"
                    :key="index"
                    v-model="computedValue"
                    v-bind="$attrs"
                    :disabled="disabled"
                    :value="index"
                >
                    &nbsp;{{ option }}
                </biz-radio>
            </div>
        </template>

        <template v-else>
            <div
                v-for="(option, index) in options"
                :key="index"
                class="control"
            >
                <biz-radio
                    v-model="computedValue"
                    v-bind="$attrs"
                    :disabled="disabled"
                    :value="index"
                >
                    &nbsp;{{ option }}
                </biz-radio>
            </div>
        </template>

        <slot name="note" />

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizFormField from '@/Biz/Form/Field';
    import BizRadio from '@/Biz/Radio';
    import BizInputError from '@/Biz/InputError';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormRadio',

        components: {
            BizFormField,
            BizRadio,
            BizInputError,
        },

        inheritAttrs: false,

        props: {
            label: {
                type: String,
                default: ''
            },
            message: {
                type: [Array, Object, String],
                default: undefined
            },
            modelValue: {
                type: [String, Number, Boolean, null],
                required: true
            },
            disabled: {
                type: Boolean,
                default: false
            },
            options: {
                type: [Array, Object],
                required: true,
            },
            required: {
                type: Boolean,
                default: false
            },
            layout: {
                type: String,
                default: "vertical",
            }
        },

        emits: [
            'update:modelValue',
        ],

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },
    };
</script>
