<template>
    <sdb-link
        v-if="isInternalLink"
        class="navbar-item"
        :href="url"
    >
        <slot />
    </sdb-link>
    <a
        v-else
        class="navbar-item"
        :href="url"
    >
        <slot />
    </a>
</template>

<script>
    import SdbLink from '@/Sdb/Link';

    export default {
        name: 'SdbNavbarItem',

        components: {
            SdbLink,
        },

        props: {
            isInternalLink: {
                type: Boolean,
                default: false,
            },
            url: {
                type: String,
                required: true
            },
        },
    };
</script>
