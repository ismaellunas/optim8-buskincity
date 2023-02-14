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
                <span>Fill in your email and password to login.</span>
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

                    <biz-recaptcha
                        ref="recaptcha"
                        :site-key="recaptchaSiteKey"
                        @on-verify="recaptchaVerify"
                    />

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
    import BizRecaptcha from '@/Biz/Recaptcha';
    import LayoutAdmin from '@/Pages/Auth/Admin/LayoutAdmin';
    import icon from '@/Libs/icon-class';

    export default {
        components: {
            BizButton,
            BizCheckbox,
            BizErrorNotifications,
            BizFlashNotifications,
            BizFormInput,
            BizFormPassword,
            BizLink,
            BizRecaptcha,
            LayoutAdmin,
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            canResetPassword: Boolean,
            recaptchaSiteKey: { type: [String, null], default: null },
            status: {type: String, default: ""},
        },

        data() {
            return {
                canLogin: true,
                canRegister: true,
                form: this.$inertia.form({
                    email: '',
                    password: '',
                    remember: false,
                }),
                icon,
            }
        },

        methods: {
            onSubmit() {
                this.$refs.recaptcha.execute();
            },

            back() {
                window.location = "/";
            },

            recaptchaVerify(response = null) {
                this.form
                    .transform(data => ({
                        ... data,
                        remember: this.form.remember ? 'on' : '',
                        'g-recaptcha-response': response,
                    }))
                    .post(this.route('admin.login'), {
                        onStart: () => this.onStartLoadingOverlay(),
                        onError: () => this.$refs.recaptcha.onExpired(),
                        onFinish: () => {
                            this.form.reset('password');
                            this.onEndLoadingOverlay();
                        },
                    })
            },
        }
    }
</script>
