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
                                        Already have an account?
                                    </span>
                                    <sdb-button-link :href="route('login')" class="">
                                        Login
                                    </sdb-button-link>
                                </div>
                            </div>
                        </div>
                        <section class="section">
                            <div class="columns" v-bind:class="{'is-hidden': !isSocialMediaLogin}">
                                <div class="column is-9 is-offset-1">
                                    <h1 class="title">Sign Up</h1>
                                    <h2 class="subtitle">
                                        <span>Are you performer? </span>
                                        <span>Sign Up Here</span>
                                    </h2>
                                    <div class="has-text-centered">

                                        <sdb-social-media-list/>

                                        <div class="h-line-wrapper">
                                            <span class="h-line-words">or</span>
                                        </div>
                                        <a
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
                                        Create Account
                                    </h1>
                                    <h2 class="subtitle">
                                        Lorem ipsum dolor sit amet.
                                    </h2>
                                    <div class="has-text-left">

                                        <form @submit.prevent="submit">

                                            <sdb-form-input 
                                                label="First Name"
                                                v-model="form.first_name"
                                                required
                                                autofocus
                                                :message="error('first_name')"
                                            ></sdb-form-input>

                                            <sdb-form-input 
                                                label="Last Name"
                                                v-model="form.last_name"
                                                required                                                
                                                :message="error('last_name')"
                                            ></sdb-form-input>

                                            <sdb-form-input
                                                v-model="form.email"
                                                label="Email"
                                                required
                                                type="email"
                                                :message="error('email')"
                                            ></sdb-form-input>

                                            <sdb-form-input
                                                v-model="form.password"
                                                autocomplete="new-password"
                                                label="Password"
                                                type="password"
                                                :message="error('password')"
                                            ></sdb-form-input>

                                            <sdb-form-input
                                                v-model="form.password_confirmation"
                                                label="Password Confirmation"
                                                type="password"
                                                :message="error('password_confirmation')"
                                            ></sdb-form-input>

                                            <div class="flex mt-4">
                                                <div class="columns is-gapless">
                                                    <div class="column is-two-thirds">
                                                        <span>
                                                            By clicking on <b>Create Account</b> you agree with our Terms and Conditions
                                                        </span>
                                                    </div>
                                                    <div class="column is-one-third has-text-right">
                                                        <sdb-button class="button is-info" :disabled="form.processing">
                                                            Create Account
                                                        </sdb-button>
                                                    </div>
                                                </div>
                                            </div>
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
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbSocialMediaList from '@/Sdb/SocialMediaList'
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbButton from '@/Sdb/Button';

    export default {
        components: {
            SdbButtonLink,
            SdbSocialMediaList,
            SdbFormInput,
            SdbErrorNotifications,
            SdbButton
        },
        mixins: [
            MixinHasPageErrors,
        ],

        data() {
            return {
                isSocialMediaLogin: true,
                form: this.$inertia.form({
                    first_name: '',
                    last_name: '',
                    email: '',
                    password: '',
                    password_confirmation: '',
                    terms: true,
                })
            }
        },

        methods: {
            submit() {
                this.form.post(this.route('register'), {
                    onFinish: () => this.form.reset('password', 'password_confirmation'),
                })
            },
            toggleIsSocialMediaLogin() {
                this.isSocialMediaLogin = !this.isSocialMediaLogin;
            },
            backOrOpenSocialList() {
                if (!this.isSocialMediaLogin) {
                    this.toggleIsSocialMediaLogin();
                } else {
                    this.$inertia.get('/login');
                }
            },
        }
    }
</script>
