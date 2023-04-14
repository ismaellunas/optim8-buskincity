<template>
    <layout-admin title="Forgot Password">
        <template #back>
            <biz-link :href="route('admin.login')">
                <biz-icon :icon="iconBack" />
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
                        :value="$page.props.csrfToken"
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
    import BizButton from '@/Biz/Button.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizLink from '@/Biz/Link.vue';
    import BizRecaptcha from '@/Biz/Recaptcha.vue';
    import LayoutAdmin from '@/Pages/Auth/Admin/LayoutAdmin.vue';
    import { back as iconBack } from '@/Libs/icon-class';
    import { useForm } from '@inertiajs/vue3';

    export default {
        components: {
            BizButton,
            BizErrorNotifications,
            BizFormInput,
            BizIcon,
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

        setup() {
            return {
                form: useForm({
                    email: '',
                }),
                iconBack,
            };
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
