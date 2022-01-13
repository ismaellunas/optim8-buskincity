<template>
    <biz-hero class="is-primary" size="small">
        <div class="container">
            <p class="title">Frontend Dashboard</p>
        </div>
    </biz-hero>

    <div class="container py-12 mt-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <welcome />
            </div>
        </div>
    </div>
</template>

<script>
    import Layout from '@/Layouts/GuestLayout';
    import BizHero from '@/Biz/Hero';
    import Welcome from '@/Jetstream/Welcome';

    export default {
        layout: Layout,
        components: {
            BizHero,
            Welcome,
        },
    }
</script>
