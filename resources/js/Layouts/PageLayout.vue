<template>
    <nav class="navbar is-dark">
        <div class="navbar-brand">
            <a class="navbar-item" href="https://bulma.io">
                <img src="http://buskincity.spectrumweb.co.uk/assets/img/logo.png" alt="Buskin City" height="28" width="42">
            </a>
            <div class="navbar-burger burger" data-target="navbarExampleTransparentExample">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <div id="navbarExampleTransparentExample" class="navbar-menu">
            <div class="navbar-start">
                <inertia-link href="/" class="navbar-item">Home</inertia-link>
                <div class="navbar-item has-dropdown is-hoverable">
                    <inertia-link href="/" class="navbar-link">More</inertia-link>
                    <div class="navbar-dropdown is-boxed">
                        <inertia-link href="/" class="navbar-item">Page 1</inertia-link>
                        <inertia-link href="/" class="navbar-item">Page 2</inertia-link>
                    </div>
                </div>
            </div>

            <div class="navbar-end">
                <div class="navbar-item">
                    <div class="field">
                        <div class="control">
                            <input class="input" type="text" placeholder="Search . . .">
                        </div>
                    </div>
                </div>
                <inertia-link :href="route('login')" class="navbar-item pr-5">Login</inertia-link>
            </div>
        </div>
    </nav>

    <div id="main-container-wrapper">
        <div id="main-container" class="container mt-4">
            <!-- Page Content -->
            <main>
                <slot></slot>
            </main>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['socialstream', 'jetstream', 'user', 'errorBags', 'flash'],
        components: {},
        data() {
            return {
            }
        },
        methods: {}
    }
</script>
