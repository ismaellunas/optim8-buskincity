<template>
    <div
        v-for="medium in media"
        :class="class"
    >
        <slot :medium="medium"></slot>
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
