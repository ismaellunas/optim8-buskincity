<template>
    <Link>
        <slot></slot>
    </Link>
</template>

<script>
    import { Link } from '@inertiajs/inertia-vue3';

    export default {
        name: 'SdbLink',
        components: { Link }
    };
</script>
