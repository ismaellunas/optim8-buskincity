<template>
    <sdb-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <sdb-input-icon
            ref="input"
            v-bind="$attrs"
            v-model="icon"
            :disabled="disabled"
            :has-error="hasError"
            :placeholder="placeholder"
            :required="required"
            @input="$emit('update:modelValue', $event.target.value)"
            @keypress="$emit('on-keypress', $event)"
            @show-modal="openModal()"
        />

        <template #error>
            <sdb-input-error :message="message" />
        </template>

        <sdb-icon-browser
            v-if="isModalOpen"
            :icon-classes="iconClasses"
            @close="closeModal()"
            @on-selected-icon="onSelectedIcon"
        />
    </sdb-form-field>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import SdbFormField from '@/Sdb/Form/Field';
    import SdbIconBrowser from '@/Sdb/Modal/IconBrowser';
    import SdbInputError from '@/Sdb/InputError';
    import SdbInputIcon from '@/Sdb/InputIcon';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'SdbFormInputIcon',
        components: {
            SdbFormField,
            SdbIconBrowser,
            SdbInputError,
            SdbInputIcon,
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
            iconClasses: {
                type: Array,
                default:() => [],
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
            'on-keypress',
            'update:modelValue',
        ],
        setup(props, { emit }) {
            return {
                icon: useModelWrapper(props, emit),
            };
        },
        computed: {
            hasError() {
                return this.message ? true : false;
            },
        },
        methods: {
            onSelectedIcon(icon) {
                this.icon = icon;
            },
        },
    };
</script>
