<template>
    <biz-link
        v-if="isInternalLink"
        class="navbar-item"
        :href="url"
        @click="$emit('after-click')"
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
    import BizLink from '@/Biz/Link.vue';

    export default {
        name: 'BizNavbarItem',

        components: {
            BizLink,
        },

        props: {
            isInternalLink: { type: Boolean, default: false },
            url: { type: String, required: true },
        },

        emits: ['after-click'],
    };
</script>
