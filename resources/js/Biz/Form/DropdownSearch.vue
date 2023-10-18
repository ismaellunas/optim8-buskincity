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

        <template #tooltip>
            <slot name="tooltip">
                <biz-tooltip
                    v-if="tooltipMessage"
                    class="ml-1"
                    :message="tooltipMessage"
                />
            </slot>
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
    import BizDropdownSearch from '@/Biz/DropdownSearch.vue';
    import BizFormField from '@/Biz/Form/Field.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizTooltip from '@/Biz/Tooltip.vue';

    export default {
        name: 'BizFormDropdownSearch',

        components: {
            BizDropdownSearch,
            BizFormField,
            BizInputError,
            BizTooltip,
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
            tooltipMessage: {
                type: String,
                default: null,
            },
        },
    };
</script>
