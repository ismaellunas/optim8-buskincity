<template>
    <sdb-field>
        <sdb-label :is-required="isRequired">
            <slot name="label"></slot>
        </sdb-label>

        <div class="control">
            <slot></slot>
        </div>

        <slot name="error"></slot>
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
        }
    };
</script>
