<template>
    <sdb-link class="button">
        <slot></slot>
    </sdb-link>
</template>

<script>
    import SdbLink from '@/Sdb/Link';

    export default {
        components: {
            SdbLink,
        }
    }
</script>
