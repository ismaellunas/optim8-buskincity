<template>
    <p>
        {{ textInfo }}
    </p>
</template>

<script>
    export default {
        name: 'BizTableInfo',

        props: {
            records: { type: Object, required: true },
        },

        computed: {
            textInfo() {
                let records = this.records;

                return `Showing ${records.from} to ${records.to} of ${records.total} entries`;
            },
        },
    }
</script>