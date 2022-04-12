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

        <template v-if="layout == 'horizontal'">
            <div class="control">
                <biz-checkbox
                    v-for="(option, index) in options"
                    :key="index"
                    v-model:checked="computedValue"
                    label-class="mr-4"
                    :disabled="disabled"
                    :value="index"
                >
                    &nbsp;
                    <span
                        v-if="isRaw"
                        v-html="option"
                    />
                    <span v-else> {{ option }} </span>
                </biz-checkbox>
            </div>
        </template>
        <template v-else>
            <div
                v-for="(option, index) in options"
                :key="index"
                class="control"
            >
                <biz-checkbox
                    v-model:checked="computedValue"
                    :disabled="disabled"
                    :value="index"
                >
                    &nbsp;
                    <span
                        v-if="isRaw"
                        v-html="option"
                    />
                    <span v-else> {{ option }} </span>
                </biz-checkbox>
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
    import BizCheckbox from '@/Biz/Checkbox';
    import BizInputError from '@/Biz/InputError';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormChecbox',

        components: {
            BizFormField,
            BizCheckbox,
            BizInputError,
        },

        props: {
            isRaw: {
                type: Boolean,
                default: false
            },
            label: {
                type: String,
                default: undefined,
            },
            message: {
                type: [Array, Object, String],
                default: undefined
            },
            modelValue: {
                type: Array,
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
                default: 'vertical',
                validator: function (value) {
                    return ['vertical', 'horizontal'].indexOf(value) !== -1;
                },
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
    };
</script>
