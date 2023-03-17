<template>
    <inertia-link>
        <slot />
    </inertia-link>
</template>

<script>
    import { Link as InertiaLink } from '@inertiajs/vue3';

    export default {
        name: 'BizLink',
        components: { InertiaLink }
    };
</script>
