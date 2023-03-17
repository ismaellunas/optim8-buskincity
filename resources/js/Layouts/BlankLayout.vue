<template>
    <div>
        <inertia-head>
            <link
                rel="stylesheet"
                :href="$page.props.css.frontend.app"
            >
        </inertia-head>

        <slot />
    </div>
</template>

<script>
    import { Head as InertiaHead } from '@inertiajs/vue3';

    export default {
        name: 'LayoutBlank',

        components: {
            InertiaHead,
        },
    };
</script>
