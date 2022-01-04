<template>
    <sdb-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <template v-if="layout == 'horizontal'">
            <div class="control">
                <sdb-radio
                    v-for="(option, index) in options"
                    :key="index"
                    v-model="computedValue"
                    v-bind="$attrs"
                    :disabled="disabled"
                    :value="index"
                >
                    &nbsp;{{ option }}
                </sdb-radio>
            </div>
        </template>

        <template v-else>
            <div
                v-for="(option, index) in options"
                :key="index"
                class="control"
            >
                <sdb-radio
                    v-model="computedValue"
                    v-bind="$attrs"
                    :disabled="disabled"
                    :value="index"
                >
                    &nbsp;{{ option }}
                </sdb-radio>
            </div>
        </template>

        <template #error>
            <sdb-input-error :message="message" />
        </template>
    </sdb-form-field>
</template>

<script>
    import SdbFormField from '@/Sdb/Form/Field';
    import SdbRadio from '@/Sdb/Radio';
    import SdbInputError from '@/Sdb/InputError';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'SdbFormRadio',

        components: {
            SdbFormField,
            SdbRadio,
            SdbInputError,
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
