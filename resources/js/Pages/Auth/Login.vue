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
                                    <inertia-link :href="route('register')" class="">
                                        <button class="button">
                                            Sign Up
                                        </button>
                                    </inertia-link>
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
                                        <jet-validation-errors class="mb-4" />
                                        <form @submit.prevent="submit">
                                            <div class="field">
                                                <jet-label for="email" value="Email" />
                                                <div class="control">
                                                    <jet-input
                                                        id="email"
                                                        type="email"
                                                        v-model="form.email"
                                                        required
                                                        autofocus
                                                        placeholder="Enter your email"
                                                        />
                                                </div>
                                            </div>
                                            <div class="field">
                                                <jet-label for="password" value="Password" />
                                                <div class="control">
                                                    <jet-input
                                                        id="password"
                                                        type="password"
                                                        v-model="form.password"
                                                        required
                                                        autocomplete="current-password"
                                                        placeholder="Enter your password"
                                                        />
                                                </div>
                                            </div>
                                            <div class="field columns">
                                                <div class="column has-text-left">
                                                    <label class="checkbox">
                                                        <jet-checkbox name="remember" v-model:checked="form.remember" />
                                                        <span class="pl-1">Remember me</span>
                                                    </label>
                                                </div>
                                                <div class="column has-text-right">
                                                    <inertia-link v-if="canResetPassword" :href="route('password.request')" class="underline text-sm text-gray-600 hover:text-gray-900">
                                                        Forgot your password?
                                                    </inertia-link>
                                                </div>
                                            </div>

                                            <jet-button class="button is-block is-info is-fullwidth">
                                                Log In <i class="fa fa-sign-in" aria-hidden="true"></i>
                                            </jet-button>
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
    import JetButton from '@/Jetstream/Button'
    import JetCheckbox from '@/Jetstream/Checkbox'
    import JetInput from '@/Jetstream/Input'
    import JetLabel from '@/Jetstream/Label'
    import SdbSocialMediaList from '@/Sdb/SocialMediaList'

    export default {
        components: {
            JetButton,
            JetCheckbox,
            JetInput,
            JetLabel,
            SdbSocialMediaList,
        },

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
