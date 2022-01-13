<template>
    <div class="columns is-multiline m-0">
        <div
            v-for="record in records"
            :key="record.id"
            :class="class"
        >
            <slot :record="record"></slot>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'PostGallery',
        props: {
            records: {},
            class: {default: ['column','is-3']},
        },
    };
</script>
