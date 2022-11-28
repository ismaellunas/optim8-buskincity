<template>
    <biz-form-field
        :is-required="required"
        :label-class="{'is-size-7': isSmall}"
    >
        <template
            v-if="label"
            #label
        >
            {{ label }}
        </template>

        <biz-dropdown-search
            v-bind="$attrs"
            :disabled="disabled"
            :placeholder="placeholder"
            :is-trigger-button="isTriggerButton"
            :is-small="isSmall"
            :is-fullwidth="isFullwidth"
        >
            <template #trigger>
                <slot name="trigger" />
            </template>

            <slot />
        </biz-dropdown-search>

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizFormField from '@/Biz/Form/Field';
    import BizInputError from '@/Biz/InputError';
    import BizDropdownSearch from '@/Biz/DropdownSearch';

    export default {
        name: 'BizFormDropdownSearch',

        components: {
            BizFormField,
            BizInputError,
            BizDropdownSearch,
        },

        inheritAttrs: false,

        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            label: {
                type: String,
                default: "",
            },
            message: {
                type: Object,
                default: () => {},
            },
            placeholder: {
                type: String,
                default: "Search ..."
            },
            required: {
                type: Boolean,
                default: false
            },
            isTriggerButton: {
                type: Boolean,
                default: true
            },
            isSmall: {
                type: Boolean,
                default: false
            },
            isFullwidth: {
                type: Boolean,
                default: false
            },
        },
    };
</script>
