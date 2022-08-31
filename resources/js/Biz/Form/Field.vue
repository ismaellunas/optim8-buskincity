<template>
    <biz-field>
        <biz-label
            v-if="hasLabelSlot"
            :class="labelClass"
            :is-required="isRequired"
        >
            <slot name="label" />

            <template
                v-if="hasLabelAddons"
                #labelAddons
            >
                <slot name="labelAddons" />
            </template>
        </biz-label>

        <slot />

        <slot name="error" />
    </biz-field>
</template>

<script>
    import BizField from '@/Biz/Field';
    import BizLabel from '@/Biz/Label';

    export default {
        name: 'BizFormField',

        components: {
            BizField,
            BizLabel,
        },

        props: {
            isRequired: {
                type: Boolean,
                default: false,
            },
            labelClass: {
                type: [Array, Object, String],
                default: '',
            },
        },

        computed: {
            hasLabelSlot() {
                return !!this.$slots.label;
            },

            hasLabelAddons() {
                return !!this.$slots.labelAddons;
            },
        },
    };
</script>
