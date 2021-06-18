<template>
    <section class="hero is-fullheight">
        <div class="hero-body">
            <div class="container has-text-centered">
                <div class="columns">
                    <div class="column is-two-fifths has-text-left">
                        <div class="card">
                            <div class="card-image">
                                <figure class="image is-3by4">
                                    <img src="https://dummyimage.com/550x715/e5e5e5/ffffff.jpg">
                                </figure>
                            </div>
                        </div>
                    </div>
                    <div class="column is-three-fifths has-text-left">
                        <div class="level">
                            <div class="level-left">
                                <div class="level-item"></div>
                            </div>
                            <div class="level-right">
                                <div class="level-item">
                                    <span class=mr-3>
                                        Already have an account?
                                    </span>
                                    <inertia-link :href="route('login')" class="">
                                        <button class="button">
                                            Log In
                                        </button>
                                    </inertia-link>
                                </div>
                            </div>
                        </div>
                        <section class="section">
                            <div class="columns">
                                <div class="column is-9 is-offset-1">
                                    <h1 class="title">Sign Up</h1>
                                    <h2 class="subtitle">
                                        <span>
                                            Are you a performer ?
                                        </span>
                                        <inertia-link v-if="canRegister" :href="route('register')" class="">
                                            Sign Up Here
                                        </inertia-link>
                                    </h2>
                                    <div class="has-text-centered">
                                        <a v-if="$page.props.socialstream.providers.includes('google')" :href="route('oauth.redirect', 'google')" class="box">
                                            <i class="fab fa-google"></i>
                                            Continue with <b>Google</b>
                                        </a>
                                        <div class="box">
                                            <i class="fab fa-facebook"></i> Continue with <b>Facebook</b>
                                        </div>
                                        <div class="box">
                                            <i class="fab fa-twitter"></i> Continue with <b>Twitter</b>
                                        </div>
                                        <!--
                                            <a v-if="$page.props.socialstream.providers.includes('facebook')" :href="route('oauth.redirect', 'facebook')" class="box">
                                            <i class="fab fa-facebook"></i> Continue with <b>Facebook</b>
                                            </a>
                                            <a v-if="$page.props.socialstream.providers.includes('twitter')" :href="route('oauth.redirect', 'twitter')" class="box">
                                            <i class="fab fa-twitter"></i> Continue with <b>Twitter</b>
                                            </a>
                                        -->
                                        <div style="width:100%; text-align:center; border-bottom: 1px solid #000; line-height:0.1em; margin:10px 0 20px;">
                                            <span style="background:#fff; padding:0 10px;">
                                                or
                                            </span>
                                        </div>
                                        <inertia-link v-if="canRegister" :href="route('register')" class="box">
                                            <i class="fas fa-envelope"></i> Continue with <b>Email</b>
                                        </inertia-link>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
    import GoogleIcon from '@/Socialstream/ProviderIcons/GoogleIcon';

    export default {
        props: {
            canLogin: Boolean,
            canRegister: Boolean,
            laravelVersion: String,
            phpVersion: String,
        },
        components: {
            GoogleIcon,
        },
    }
</script>
