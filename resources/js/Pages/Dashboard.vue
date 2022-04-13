<template>
    <layout-guest>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Frontend Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                    <welcome />
                </div>
            </div>
        </div>
    </layout-guest>
</template>

<script>
    import LayoutGuest from '@/Layouts/GuestLayout';
    import Welcome from '@/Jetstream/Welcome';

    export default {
        components: {
            LayoutGuest,
            Welcome,
        },
    }
</script>
