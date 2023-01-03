<template>
    <layout-admin title="Forgot Password">
        <template #back>
            <biz-link :href="route('admin.login')">
                <span class="icon">
                    <i :class="icon.back" />
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
                ref="formForgotPassword"
                method="post"
                @submit.prevent="onSubmit()"
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

                <biz-recaptcha
                    ref="recaptcha"
                    :site-key="recaptchaSiteKey"
                    @on-verify="recaptchaVerify"
                />

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
    import BizRecaptcha from '@/Biz/Recaptcha';
    import LayoutAdmin from '@/Pages/Auth/Admin/LayoutAdmin';
    import icon from '@/Libs/icon-class';

    export default {
        components: {
            BizButton,
            BizErrorNotifications,
            BizFormInput,
            BizLink,
            BizRecaptcha,
            LayoutAdmin,
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            failed: { type: String, default: '' },
            recaptchaSiteKey: { type: [String, null], default: null },
            status: { type: String, default: '' },
        },

        data() {
            return {
                form: this.$inertia.form({
                    email: '',
                }),
                csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                icon,
            }
        },

        methods: {
            onSubmit() {
                this.onStartLoadingOverlay();

                this.$refs.recaptcha.execute();
            },

            recaptchaVerify() {
                this.$refs.formForgotPassword.submit();
            },
        }
    }
</script>
