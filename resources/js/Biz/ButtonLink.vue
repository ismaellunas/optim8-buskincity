<template>
    <biz-link class="button">
        <slot />
    </biz-link>
</template>

<script>
    import BizLink from '@/Biz/Link';

    export default {
        components: {
            BizLink,
        }
    }
</script>
