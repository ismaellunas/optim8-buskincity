<template>
    <biz-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <biz-input-file
            v-model="file"
            v-bind="$attrs"
            :accept="acceptedTypes"
            :disabled="disabled"
            :is-name-displayed="isNameDisplayed"
            @on-file-picked="$emit('on-file-picked', $event)"
        />

        <slot name="note">
            <p
                v-if="notes"
                class="help is-info"
            >
                <ul>
                    <li
                        v-for="note, index in notes"
                        :key="index"
                    >
                        {{ note }}
                    </li>
                </ul>
            </p>
        </slot>

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizFormField from '@/Biz/Form/Field';
    import BizInputFile from '@/Biz/InputFile';
    import BizInputError from '@/Biz/InputError';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormFile',

        components: {
            BizFormField,
            BizInputFile,
            BizInputError,
        },

        inheritAttrs: false,

        props: {
            acceptedTypes: {
                type: Array,
                default:() => [],
            },
            isNameDisplayed: {
                type: Boolean,
                default: true,
            },
            notes: {
                type: Array,
                default: () => [],
            },
            label: {
                type: String,
                default: null,
            },
            message: {
                type: Object,
                default: () => {},
            },
            modelValue: {
                type: [File, null],
                required: true,
            },
            disabled: {
                type: Boolean,
                default: false
            },
            required: {
                type: Boolean,
                default: false
            },
        },

        emits: [
            'on-file-picked',
            'update:modelValue',
        ],

        setup(props, { emit }) {
            return {
                file: useModelWrapper(props, emit),
            };
        },
    };
</script>
