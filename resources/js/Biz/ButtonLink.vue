<template>
    <biz-link class="button">
        <slot />
    </biz-link>
</template>

<script>
    import BizLink from '@/Biz/Link.vue';

    export default {
        components: {
            BizLink,
        }
    }
</script>
