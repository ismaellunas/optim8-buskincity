<template>
    <sdb-field>
        <sdb-label>
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
        components: {
            SdbField,
            SdbLabel,
        },
    }
</script>
