<template>
    <biz-link
        v-if="isInternalLink"
        class="navbar-item"
        :href="url"
    >
        <slot />
    </biz-link>
    <a
        v-else
        class="navbar-item"
        :href="url"
    >
        <slot />
    </a>
</template>

<script>
    import BizLink from '@/Biz/Link';

    export default {
        name: 'BizNavbarItem',

        components: {
            BizLink,
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
