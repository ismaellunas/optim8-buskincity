<template>
    <div class="columns is-multiline m-0">
        <div
            v-for="record in records"
            :key="record.id"
            :class="recordClass"
        >
            <slot :record="record" />
        </div>
    </div>
</template>

<script>
    export default {
        name: 'PostGallery',

        props: {
            records: { type: Array, required: true },
            class: { type: [Array, String, Object], default: () => [
                'column',
                'is-3'
            ] },
        },

        computed: {
            recordClass() {
                return this.class;
            },
        },
    };
</script>
