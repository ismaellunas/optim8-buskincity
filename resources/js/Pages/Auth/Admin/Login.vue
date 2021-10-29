<template>
    <Head>
        <link
            v-for="css in $page.props.css.backend"
            rel="stylesheet"
            :href="css"
        >
    </Head>

    <section class="hero is-fullheight">
        <div class="hero-body">
            <div class="container has-text-centered">
                <div class="columns">
                    <div class="column is-three-fifths has-text-left">
                        <div class="level">
                            <div class="level-left">
                                <div class="level-item">
                                    <a @click.prevent="back">
                                        <span class="icon"><i class="fas fa-arrow-left"></i></span>
                                        <span>Back</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <section class="section">
                            <div class="columns">
                                <div class="column is-9 is-offset-1">
                                    <h1 class="title has-text-centered">
                                        Welcome Back
                                    </h1>
                                    <h2 class="subtitle has-text-centered">
                                        <span>Lorem ipsum dolor sit amet.</span>
                                    </h2>
                                    <div class="has-text-left">

                                        <sdb-error-notifications :errors="$page.props.errors"/>

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
                                                    <sdb-link v-if="canResetPassword" :href="route('password.request')">
                                                        Forgot your password?
                                                    </sdb-link>
                                                </div>
                                            </div>

                                            <jet-button class="button is-block is-info is-fullwidth">
                                                Log In <i class="fas fa-sign-in-alt"></i>
                                            </jet-button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="column is-two-fifths has-text-left">
                        <div class="card">
                            <div class="card-image">
                                <figure class="image is-3by4">
                                    <img src="https://dummyimage.com/550x715/e5e5e5/ffffff.jpg">
                                </figure>
                            </div>
                        </div>
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
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbLink from '@/Sdb/Link';
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            Head,
            JetButton,
            JetCheckbox,
            JetInput,
            JetLabel,
            SdbButtonLink,
            SdbErrorNotifications,
            SdbLink,
        },

        props: {
            canResetPassword: Boolean,
            status: String
        },

        data() {
            return {
                canLogin: true,
                canRegister: true,
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
                    .post(this.route('admin.login'), {
                        onFinish: () => this.form.reset('password'),
                    })
            },
            back() {
                this.$inertia.get('/');
            },
        }
    }
</script>
