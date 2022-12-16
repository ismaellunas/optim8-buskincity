<template>
    <layout-admin title="Two Factor Authentication">
        <template #back>
            <a @click.prevent="redirectBack()">
                <span class="icon">
                    <i :class="icon.back" />
                </span>
                <span>Back</span>
            </a>
        </template>

        <template #default>
            <p class="mb-4">
                <template v-if="! recovery">
                    Please confirm access to your account by entering the authentication code provided by your authenticator application.
                </template>

                <template v-else>
                    Please confirm access to your account by entering one of your emergency recovery codes.
                </template>
            </p>
            <div class="has-text-left">
                <biz-error-notifications
                    :errors="$page.props.errors"
                />

                <form @submit.prevent="onSubmit()">
                    <div v-if="! recovery">
                        <biz-form-input
                            ref="code"
                            v-model="form.code"
                            label="Code"
                            required
                            type="text"
                            inputmode="numeric"
                            autofocus
                            autocomplete="one-time-code"
                            :message="error('code')"
                        />
                    </div>

                    <div v-else>
                        <biz-form-input
                            ref="recovery_code"
                            v-model="form.recovery_code"
                            label="Recovery Code"
                            required
                            type="text"
                            autocomplete="one-time-code"
                            :message="error('recovery_code')"
                        />
                    </div>

                    <template
                        v-if="isRecaptchaAvailable"
                    >
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
                    </template>


                    <div class="mt-4">
                        <biz-button
                            type="button"
                            class="button"
                            :disabled="form.processing"
                            @click.prevent="toggleRecovery"
                        >
                            <template v-if="! recovery">
                                Use a recovery code
                            </template>

                            <template v-else>
                                Use an authentication code
                            </template>
                        </biz-button>
                        <biz-button
                            class="button is-info ml-4"
                            :disabled="form.processing"
                        >
                            Log In
                        </biz-button>
                    </div>
                </form>
            </div>
        </template>
    </layout-admin>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFormInput from '@/Biz/Form/Input';
    import LayoutAdmin from '@/Pages/Auth/Admin/LayoutAdmin';
    import icon from '@/Libs/icon-class';
    import { VueRecaptcha } from 'vue-recaptcha';

    export default {
        components: {
            BizButton,
            BizErrorNotifications,
            BizFormInput,
            LayoutAdmin,
            VueRecaptcha,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            recaptchaSiteKey: { type: [String, null], default: null },
        },

        data() {
            return {
                recovery: false,
                form: this.$inertia.form({
                    code: '',
                    recovery_code: '',
                }),
                icon,
                isRecaptchaError: false,
            }
        },

        computed: {
            isRecaptchaAvailable() {
                return !!this.recaptchaSiteKey;
            },
        },

        methods: {
            toggleRecovery() {
                this.recovery ^= true

                this.$nextTick(() => {
                    if (this.recovery) {
                        this.$refs.recovery_code.$refs.input.focus()
                        this.form.code = '';
                    } else {
                        this.$refs.code.$refs.input.focus()
                        this.form.recovery_code = ''
                    }
                })
            },

            onSubmit() {
                if (this.isRecaptchaAvailable) {
                    this.recaptchaExecute();
                } else {
                    this.submit();
                }
            },

            submit(recaptchaResponse = null) {
                this.form
                    .transform(data => ({
                        ... data,
                        'g-recaptcha-response': recaptchaResponse,
                    }))
                    .post(this.route('admin.two-factor.login.attempt'));
            },

            redirectBack() {
                window.history.back();
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
