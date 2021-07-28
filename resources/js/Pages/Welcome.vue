<template>
    <Head>
        <title>Welcome</title>
        <meta head-key="description" name="description" content="Welcome" />
    </Head>

    <section class="hero">
        <figure class="image">
            <img src="https://dummyimage.com/1002x400/808080/fff.jpg&text=Platform+752" alt="hero-image">
        </figure>
    </section>
</template>

<script>
    import PageLayout from '@/Layouts/PageLayout';
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        layout: PageLayout,
        props: {
        },
        components: {
            Head,
        },
    }
</script>
