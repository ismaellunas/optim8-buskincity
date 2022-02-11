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

            <form method="post">
                <div class="mb-4">
                    <input
                        type="hidden"
                        name="_token"
                        :value="csrfToken"
                    >

                    <biz-form-input
                        name="email"
                        v-model="form.email"
                        label="Email"
                        required
                        type="email"
                        placeholder="Enter your email"
                    />
                </div>

                <vue-recaptcha
                    :sitekey="$page.props.recaptchaSiteKey"
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
    import BizButton from '@/Biz/Button';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFormInput from '@/Biz/Form/Input';
    import BizLink from '@/Biz/Link';
    import LayoutAdmin from '@/Pages/Auth/Admin/LayoutAdmin';
    import { VueRecaptcha } from 'vue-recaptcha'

    export default {
        components: {
            BizButton,
            BizErrorNotifications,
            BizFormInput,
            BizLink,
            LayoutAdmin,
            VueRecaptcha,
        },

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
        }
    }
</script>
