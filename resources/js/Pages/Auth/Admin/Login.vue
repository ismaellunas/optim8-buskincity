<template>
    <layout-admin title="Login">
        <template #back>
            <a @click.prevent="back">
                <biz-icon :icon="iconBack" />
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
                        name="email"
                        placeholder="Enter your email"
                        required
                        autofocus
                    />

                    <biz-form-password
                        v-model="form.password"
                        autocomplete="current-password"
                        label="Password"
                        name="password"
                        placeholder="Enter your password"
                        :required="true"
                    />

                    <div class="field columns">
                        <div class="column has-text-left">
                            <biz-checkbox
                                v-model:checked="form.remember"
                                name="remember"
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
                        action="admin_login"
                        :site-key="recaptchaSiteKey"
                        @on-verify="recaptchaVerify"
                    />

                    <biz-button
                        class="is-block is-info is-fullwidth"
                    >
                        <span>Log In</span>
                        <biz-icon :icon="iconSignIn" />
                    </biz-button>
                </form>
            </div>
        </template>
    </layout-admin>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import LayoutAdmin from '@/Pages/Auth/Admin/LayoutAdmin.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFlashNotifications from '@/Biz/FlashNotifications.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormPassword from '@/Biz/Form/Password.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizLink from '@/Biz/Link.vue';
    import BizRecaptcha from '@/Biz/Recaptcha.vue';
    import { back as iconBack, signIn as iconSignIn } from '@/Libs/icon-class';
    import { useForm } from '@inertiajs/vue3';

    export default {
        name: 'AuthAdminLogin',

        components: {
            BizButton,
            BizCheckbox,
            BizErrorNotifications,
            BizFlashNotifications,
            BizFormInput,
            BizFormPassword,
            BizIcon,
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
                form: useForm({
                    email: '',
                    password: '',
                    remember: false,
                }),
                iconBack,
                iconSignIn,
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
                        onFinish: () => {
                            this.form.reset('password');
                            this.onEndLoadingOverlay();
                        },
                    })
            },
        }
    }
</script>
