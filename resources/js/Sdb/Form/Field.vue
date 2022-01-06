<template>
    <sdb-field>
        <sdb-label
            v-if="hasLabelSlot"
            :is-required="isRequired"
        >
            <slot name="label" />
        </sdb-label>

        <div class="control">
            <slot />
        </div>

        <slot name="error" />
    </sdb-field>
</template>

<script>
    import SdbField from '@/Sdb/Field';
    import SdbLabel from '@/Sdb/Label';

    export default {
        name: 'SdbFormField',
        components: {
            SdbField,
            SdbLabel,
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
