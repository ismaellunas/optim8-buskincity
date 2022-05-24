<template>
    <biz-field>
        <biz-label
            v-if="hasLabelSlot"
            :class="labelClass"
            :is-required="isRequired"
        >
            <slot name="label" />
        </biz-label>

        <div :class="['control', controlClasses]">
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
            },
            hasLeftIcon: {
                type: Boolean,
                default: false,
            },
            labelClass: {
                type: [Array, Object, String],
                default: '',
            },
        },

        computed: {
            controlClasses() {
                const classes = [];

                if (this.hasLeftIcon) {
                    classes.push('has-icons-left');
                }

                return classes;
            },

            hasLabelSlot() {
                return !!this.$slots.label;
            }
        },
    };
</script>
