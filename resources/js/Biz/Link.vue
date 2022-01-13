<template>
    <Link>
        <slot />
    </Link>
</template>

<script>
    import { Link } from '@inertiajs/inertia-vue3';

    export default {
        name: 'BizLink',
        components: { Link }
    };
</script>
