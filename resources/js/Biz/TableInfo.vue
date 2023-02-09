<template>
    <p class="mb-2">
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

                return `Showing ${records.from ?? 0} to ${records.to ?? 0} of ${records.total ?? 0} entries`;
            },
        },
    }
</script>