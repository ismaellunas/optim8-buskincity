<template>
    <layout-admin title="Login">
        <template #back>
            <a @click.prevent="back">
                <span class="icon">
                    <i :class="icon.back" />
                </span>
                <span>Back</span>
            </a>
        </template>

        <template #default>
            <h1 class="title has-text-centered">
                Welcome Back
            </h1>
            <h2 class="subtitle has-text-centered">
                <span>Lorem ipsum dolor sit amet.</span>
            </h2>
            <div class="has-text-left">
                <biz-error-notifications :errors="$page.props.errors" />

                <biz-flash-notifications :flash="$page.props.flash" />

                <form @submit.prevent="onSubmit()">
                    <biz-form-input
                        v-model="form.email"
                        label="Email"
                        type="email"
                        placeholder="Enter your email"
                        required
                        autofocus
                    />

                    <biz-form-password
                        v-model="form.password"
                        autocomplete="current-password"
                        label="Password"
                        placeholder="Enter your password"
                        :required="true"
                    />

                    <div class="field columns">
                        <div class="column has-text-left">
                            <biz-checkbox
                                name="remember"
                                v-model:checked="form.remember"
                            >
                                <span class="pl-1">Remember me</span>
                            </biz-checkbox>
                        </div>
                        <div class="column has-text-right">
                            <biz-link
                                v-if="canResetPassword"
                                :href="route('admin.password.request')"
                            >
                                Forgot your password?
                            </biz-link>
                        </div>
                    </div>

                    <vue-recaptcha
                        ref="vueRecaptcha"
                        :sitekey="recaptchaSiteKey"
                        size="invisible"
                        theme="light"
                        @expired="recaptchaExpired"
                        @error="recaptchaFailed"
                        @verify="recaptchaVerify"
                    />

                    <span
                        v-if="isRecaptchaError"
                        class="has-text-danger"
                    >
                        Please check the captcha!
                    </span>

                    <biz-button
                        class="is-block is-info is-fullwidth"
                    >
                        Log In <i :class="icon.signIn" />
                    </biz-button>
                </form>
            </div>
        </template>
    </layout-admin>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButton from '@/Biz/Button';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFlashNotifications from '@/Biz/FlashNotifications';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormPassword from '@/Biz/Form/Password';
    import BizLink from '@/Biz/Link';
    import LayoutAdmin from '@/Pages/Auth/Admin/LayoutAdmin';
    import icon from '@/Libs/icon-class';
    import { VueRecaptcha } from 'vue-recaptcha';

    export default {
        components: {
            BizButton,
            BizCheckbox,
            BizErrorNotifications,
            BizFlashNotifications,
            BizFormInput,
            BizFormPassword,
            BizLink,
            LayoutAdmin,
            VueRecaptcha,
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            canResetPassword: Boolean,
            status: {type: String, default: ""},
            recaptchaSiteKey: { type: [String, null], default: null },
        },

        data() {
            return {
                canLogin: true,
                canRegister: true,
                form: this.$inertia.form({
                    email: '',
                    password: '',
                    remember: false
                }),
                icon,
                isRecaptchaError: false,
            }
        },

        methods: {
            onSubmit() {
                this.recaptchaExecute();
            },

            submit(recaptchaResponse) {
                this.form
                    .transform(data => ({
                        ... data,
                        remember: this.form.remember ? 'on' : '',
                        'g-recaptcha-response': recaptchaResponse,
                    }))
                    .post(this.route('admin.login'), {
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: () => {
                            this.form.reset('password');
                            this.onEndLoadingOverlay();
                        },
                    })
            },

            back() {
                window.location = "/";
            },

            recaptchaExecute() {
                this.$refs.vueRecaptcha.execute();
            },

            recaptchaExpired() {
                this.$refs.vueRecaptcha.reset();
            },

            recaptchaFailed() {
                this.isRecaptchaError = true;
            },

            recaptchaVerify(response) {
                this.submit(response);
            },
        }
    }
</script>
