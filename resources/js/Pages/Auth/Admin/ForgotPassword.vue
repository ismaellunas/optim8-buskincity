<template>
    <layout-admin>
        <template #back>
            <biz-link :href="route('admin.login')">
                <span class="icon">
                    <i class="fas fa-arrow-left" />
                </span>
                <span>Back</span>
            </biz-link>
        </template>

        <template #default>
            <div class="mb-4">
                Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
            </div>

            <div
                v-if="failed"
                class="notification is-danger"
            >
                {{ failed }}
            </div>

            <div
                v-if="status"
                class="notification is-success"
            >
                {{ status }}
            </div>

            <biz-error-notifications
                :errors="$page.props.errors"
            />

            <form
                method="post"
                @submit="setLoader()"
            >
                <div class="mb-4">
                    <input
                        type="hidden"
                        name="_token"
                        :value="csrfToken"
                    >

                    <biz-form-input
                        v-model="form.email"
                        name="email"
                        label="Email"
                        required
                        type="email"
                        placeholder="Enter your email"
                    />
                </div>

                <vue-recaptcha
                    ref="vueRecaptcha"
                    :sitekey="$page.props.recaptchaSiteKey"
                    size="normal"
                    theme="light"
                    @expire="recaptchaExpired"
                    @fail="recaptchaFailed"
                />

                <span
                    v-if="isRecaptchaError"
                    class="has-text-danger"
                >
                    Please check the captcha!
                </span>

                <div class="mt-4">
                    <biz-button
                        class="button is-info"
                        type="submit"
                        :disabled="form.processing"
                    >
                        Email Password Reset Link
                    </biz-button>
                </div>
            </form>
        </template>
    </layout-admin>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButton from '@/Biz/Button';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFormInput from '@/Biz/Form/Input';
    import BizLink from '@/Biz/Link';
    import LayoutAdmin from '@/Pages/Auth/Admin/LayoutAdmin';
    import vueRecaptcha from 'vue3-recaptcha2';

    export default {
        components: {
            BizButton,
            BizErrorNotifications,
            BizFormInput,
            BizLink,
            LayoutAdmin,
            vueRecaptcha,
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            failed: {
                type: String,
                default: '',
            },
            status: {
                type: String,
                default: '',
            }
        },

        data() {
            return {
                isRecaptchaError: false,
                form: this.$inertia.form({
                    email: '',
                    'g-recaptcha-response': null,
                }),
                csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        },

        methods: {
            setLoader() {
                this.onStartLoadingOverlay();
            },

            recaptchaExpired() {
                this.$refs.vueRecaptcha.reset();
            },

            recaptchaFailed() {
                this.isRecaptchaError = true;
            }
        }
    }
</script>
