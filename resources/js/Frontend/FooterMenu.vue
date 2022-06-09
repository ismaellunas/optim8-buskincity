<template>
    <footer class="pt-6 pb-6">
        <div class="container">
            <div class="columns is-multiline">
                <div class="column is-3">
                    <img :src="logoImageUrl" style="max-width:160px">
                </div>
                <div class="column is-3">
                    <aside class="menu">
                        <p class="menu-label">About</p>
                        <ul class="menu-list">
                            <li><a href="#">Blog</a></li>
                            <li><a href="#">About</a></li>
                            <li><a href="#">Contact us</a></li>
                        </ul>
                    </aside>
                </div>
                <div class="column is-3">
                    <aside class="menu">
                        <p class="menu-label">Performers</p>
                        <ul class="menu-list">
                            <li><a href="#">Street Performers</a></li>
                            <li><a href="#">Become a Performer</a></li>
                        </ul>
                    </aside>
                </div>
                <div class="column is-3">
                    <aside class="menu">
                        <p class="menu-label">General</p>
                        <ul class="menu-list">
                            <li><a href="#">Terms & Conditions</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Cookie Policy</a></li>
                        </ul>
                    </aside>
                </div>
                <div class="column is-12">
                    <p class="is-size-7 has-text-centered mt-5">
                        © Copyright 2022, {{ appName }}
                    </p>
                </div>
            </div>
        </div>
    </footer>
</template>

<script>
    import { computed } from 'vue';
    import { usePage } from '@inertiajs/inertia-vue3';
    import { appName } from '@/Libs/defaults';

    export default {
        name: 'FrontendFooterMenu',

        setup() {
            const logoImageUrl = computed(() => {
                return (
                    usePage().props.value.logoUrl
                    ?? "https://dummyimage.com/48x28/e5e5e5/000000.png&text=Logo"
                );
            });

            return {
                appName: appName,
                logoImageUrl,
            };
        },
    }
</script>