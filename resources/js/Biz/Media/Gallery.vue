<template>
    <div
        v-for="medium in media"
        :class="class"
        :key="medium.id"
    >
        <slot :medium="medium" />
    </div>
</template>

<script>
    export default {
        name: 'MediaGallery',
        props: {
            media: {},
            class: {default: ['column','is-3']},
        },
    };
</script>
