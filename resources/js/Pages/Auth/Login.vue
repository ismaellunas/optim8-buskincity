<template>
    <Head>
        <link
            v-for="css in $page.props.css.frontend"
            rel="stylesheet"
            :href="css"
        >
    </Head>

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
                                <div class="level-item">
                                    <a @click.prevent="backOrOpenSocialList">
                                        <span class="icon"><i class="fas fa-arrow-left"></i></span>
                                        <span>Back</span>
                                    </a>
                                </div>
                            </div>
                            <div class="level-right">
                                <div class="level-item">
                                    <span class=mr-3>
                                        Don't have an account?
                                    </span>
                                    <sdb-button-link :href="route('register')">
                                        Sign Up
                                    </sdb-button-link>
                                </div>
                            </div>
                        </div>
                        <section class="section">
                            <div class="columns" v-bind:class="{'is-hidden': !isSocialMediaLogin}">
                                <div class="column is-9 is-offset-1">
                                    <h1 class="title">Log In</h1>
                                    <h2 class="subtitle">
                                        <span>Please login to continue</span>
                                    </h2>
                                    <div class="has-text-centered">

                                        <sdb-social-media-list/>

                                        <div class="h-line-wrapper">
                                            <span class="h-line-words">or</span>
                                        </div>
                                        <a
                                            v-if="canRegister"
                                            class="box"
                                            @click.prevent="toggleIsSocialMediaLogin"
                                        >
                                            <i class="fas fa-envelope"></i> Continue with <b>Email</b>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="columns" v-bind:class="{'is-hidden': isSocialMediaLogin}">
                                <div class="column is-9 is-offset-1">
                                    <h1 class="title">
                                        Welcome Back
                                    </h1>
                                    <h2 class="subtitle">
                                        <span>Lorem ipsum dolor sit amet.</span>
                                    </h2>
                                    <div class="has-text-left">

                                        <form @submit.prevent="submit">

                                            <sdb-form-input
                                                v-model="form.email"
                                                label="Email"
                                                required
                                                type="email"
                                                placeholder="Enter your email"
                                                :message="error('email')"
                                            ></sdb-form-input>

                                            <sdb-form-input
                                                v-model="form.password"
                                                autocomplete="new-password"
                                                label="Password"
                                                type="password"
                                                placeholder="Enter your password"
                                                :message="error('password')"
                                            ></sdb-form-input>

                                            <div class="field columns">
                                                <div class="column has-text-left">
                                                    <label class="checkbox">
                                                        <sdb-checkbox name="remember" v-model:checked="form.remember">
                                                            <span class="pl-1">Remember me</span>
                                                        </sdb-checkbox>
                                                    </label>
                                                </div>
                                                <div class="column has-text-right">
                                                    <sdb-link v-if="canResetPassword" :href="route('password.request')">
                                                        Forgot your password?
                                                    </sdb-link>
                                                </div>
                                            </div>

                                            <sdb-button class="button is-block is-info is-fullwidth" :disabled="form.processing">
                                                Log In <i class="fas fa-sign-in-alt"></i>
                                            </sdb-button>
                                        </form>
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
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbCheckbox from '@/Sdb/Checkbox';
    import SdbLink from '@/Sdb/Link';
    import SdbSocialMediaList from '@/Sdb/SocialMediaList'
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            Head,
            SdbFormInput,
            SdbErrorNotifications,
            SdbButton,
            SdbButtonLink,
            SdbCheckbox
            SdbLink,
            SdbSocialMediaList,
        },
        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            canResetPassword: Boolean,
            status: String
        },

        data() {
            return {
                canLogin: true,
                canRegister: true,
                isSocialMediaLogin: true,
                form: this.$inertia.form({
                    email: '',
                    password: '',
                    remember: false
                })
            }
        },

        methods: {
            submit() {
                this.form
                    .transform(data => ({
                        ... data,
                        remember: this.form.remember ? 'on' : ''
                    }))
                    .post(this.route('login'), {
                        onFinish: () => this.form.reset('password'),
                    })
            },
            toggleIsSocialMediaLogin() {
                this.isSocialMediaLogin = !this.isSocialMediaLogin;
            },
            backOrOpenSocialList() {
                if (!this.isSocialMediaLogin) {
                    this.toggleIsSocialMediaLogin();
                } else {
                    this.$inertia.get('/');
                }
            },
        }
    }
</script>
