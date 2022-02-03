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
                                    <biz-button-link :href="route('register')">
                                        Sign Up
                                    </biz-button-link>
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
                                        <biz-error-notifications :errors="$page.props.errors" />

                                        <biz-social-media-list/>

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

                                        <biz-error-notifications :errors="$page.props.errors"/>

                                        <form @submit.prevent="submit">

                                            <biz-form-input
                                                v-model="form.email"
                                                label="Email"
                                                required
                                                type="email"
                                                placeholder="Enter your email"
                                                :message="error('email')"
                                            ></biz-form-input>

                                            <biz-form-password
                                                v-model="form.password"
                                                autocomplete="current-password"
                                                label="Password"
                                                placeholder="Enter your password"
                                                :required="true"
                                            ></biz-form-password>

                                            <div class="field columns">
                                                <div class="column has-text-left">
                                                    <label class="checkbox">
                                                        <biz-checkbox name="remember" v-model:checked="form.remember">
                                                            <span class="pl-1">Remember me</span>
                                                        </biz-checkbox>
                                                    </label>
                                                </div>
                                                <div class="column has-text-right">
                                                    <biz-link v-if="canResetPassword" :href="route('password.request')">
                                                        Forgot your password?
                                                    </biz-link>
                                                </div>
                                            </div>

                                            <biz-button class="button is-block is-info is-fullwidth" :disabled="form.processing">
                                                Log In <i class="fas fa-sign-in-alt"></i>
                                            </biz-button>
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
    import LayoutBlank from '@/Layouts/BlankLayout';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormPassword from '@/Biz/Form/Password';
    import BizLink from '@/Biz/Link';
    import BizSocialMediaList from '@/Biz/SocialMediaList'

    export default {
        components: {
            BizButton,
            BizButtonLink,
            BizCheckbox,
            BizErrorNotifications,
            BizFormInput,
            BizFormPassword,
            BizLink,
            BizSocialMediaList,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        layout: LayoutBlank,

        props: {
            canResetPassword: Boolean,
            status: {type: String, default: ""},
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
                    window.location = "/";
                }
            },
        }
    };
</script>
