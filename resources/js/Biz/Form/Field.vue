<template>
    <biz-field>
        <biz-label
            v-if="hasLabelSlot"
            :is-required="isRequired"
        >
            <slot name="label" />
        </biz-label>

        <div class="control">
            <slot />
        </div>

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
            }
        },
        computed: {
            hasLabelSlot() {
                return !!this.$slots.label;
            }
        }
    };
</script>
