<template>
    <div>
        <Head>
            <link
                rel="stylesheet"
                :href="$page.props.css.frontend.app"
            >
        </Head>

        <slot />
    </div>
</template>

<script>
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        name: 'LayoutBlank',

        components: {
            Head,
        },
    };
</script>
