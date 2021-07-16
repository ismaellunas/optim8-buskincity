<template>
    <nav class="navbar is-dark">
        <div class="navbar-brand">
            <a class="navbar-item" href="">
                <img src="https://dummyimage.com/48x28/e5e5e5/000000.png&text=B+752" alt="" height="28">
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

    <section class="hero">
        <figure class="image">
            <img src="https://dummyimage.com/1002x400/808080/fff.jpg&text=Platform+752" alt="hero-image">
        </figure>
    </section>
</template>

<script>
    //import GoogleIcon from '@/Socialstream/ProviderIcons/GoogleIcon';

    export default {
        props: {
        },

        components: {
            //GoogleIcon,
        },
    }
</script>
