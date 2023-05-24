<template>
    <layout-admin title="Two Factor Authentication">
        <template #back>
            <a @click.prevent="redirectBack()">
                <biz-icon :icon="iconBack" />
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

                    <biz-recaptcha
                        ref="recaptcha"
                        :site-key="recaptchaSiteKey"
                        @on-verify="recaptchaVerify"
                    />

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
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import LayoutAdmin from '@/Pages/Auth/Admin/LayoutAdmin.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizRecaptcha from '@/Biz/Recaptcha.vue';
    import { back as iconBack, signIn as iconSignIn } from '@/Libs/icon-class';
    import { useForm } from '@inertiajs/vue3';

    export default {
        components: {
            LayoutAdmin,
            BizButton,
            BizErrorNotifications,
            BizFormInput,
            BizIcon,
            BizRecaptcha,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        props: {
            recaptchaSiteKey: { type: [String, null], default: null },
        },

        data() {
            return {
                recovery: false,
                form: useForm({
                    code: '',
                    recovery_code: '',
                }),
                iconBack,
            }
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
                this.$refs.recaptcha.execute();
            },

            redirectBack() {
                window.history.back();
            },

            recaptchaVerify(response = null) {
                this.form
                    .transform(data => ({
                        ... data,
                        'g-recaptcha-response': response,
                    }))
                    .post(this.route('admin.two-factor.login.attempt'), {
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: () => this.onEndLoadingOverlay(),
                    });
            },
        }
    }
</script>
