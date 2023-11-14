<template>
    <biz-field>
        <biz-label
            v-if="$slots.label"
            :class="labelClass"
            :is-required="isRequired"
        >
            <slot name="label" />

            <template #tooltip>
                <slot name="tooltip" />
            </template>
        </biz-label>

        <slot />

        <slot name="error" />
    </biz-field>
</template>

<script>
    import BizField from '@/Biz/Field.vue';
    import BizLabel from '@/Biz/Label.vue';

    export default {
        name: 'BizFormField',

        components: {
            BizField,
            BizLabel,
        },

        props: {
            isRequired: { type: Boolean, default: false },
            labelClass: { type: [Array, Object, String], default: '' },
        },
    };
</script>
