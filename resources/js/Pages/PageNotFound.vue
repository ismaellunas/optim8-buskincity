<template>
    <Head>
        <title>404 Page Not Found</title>
    </Head>
    <section class="hero is-light is-medium">
        <!-- Hero content: will be in the middle -->
        <div class="hero-body">
            <div class="container has-text-centered">
                <p class="title">Page Not Found</p>
                <p class="subtitle">An unexpected error has occurred. Please contact the site owner.</p>
                <sdb-button-link href="/">Home</sdb-button-link>
            </div>
        </div>
    </section>
</template>

<script>
    import PageLayout from '@/Layouts/PageLayout';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        layout: PageLayout,
        components: {
            Head,
            SdbButtonLink,
        },
        props: {
            errors: Object,
        },
    }

</script>
