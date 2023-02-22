<template>
    <biz-form-field
        :is-required="required"
        :label-class="{'is-size-7': isSmall}"
    >
        <template #label>
            {{ label }}
        </template>

        <biz-input-icon
            ref="input"
            v-bind="$attrs"
            v-model="icon"
            :disabled="disabled"
            :has-error="hasError"
            :placeholder="placeholder"
            :required="required"
            :is-small="isSmall"
            @input="$emit('update:modelValue', $event.target.value)"
            @keypress="$emit('on-keypress', $event)"
            @show-modal="openModal()"
        />

        <template #error>
            <biz-input-error :message="message" />
        </template>

        <biz-icon-browser
            v-if="isModalOpen"
            :icon-classes="iconClasses"
            :has-type="hasType"
            @close="closeModal()"
            @on-selected-icon="onSelectedIcon"
        />
    </biz-form-field>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import BizFormField from '@/Biz/Form/Field.vue';
    import BizIconBrowser from '@/Biz/Modal/IconBrowser.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizInputIcon from '@/Biz/InputIcon.vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormInputIcon',
        components: {
            BizFormField,
            BizIconBrowser,
            BizInputError,
            BizInputIcon,
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
                default: null,
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
            canRemove: {
                type: Boolean,
                default: false
            },
            hasType: {
                type: Boolean,
                default: false
            },
            isSmall: {
                type: Boolean,
                default: false
            }
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
