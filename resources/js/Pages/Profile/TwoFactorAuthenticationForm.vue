<template>
    <form-action-section>
        <template #title>
            Two Factor Authentication
        </template>

        <template #content>
            <h3 v-if="twoFactorEnabled">
                You have enabled two factor authentication.
            </h3>

            <h3 v-else>
                You have not enabled two factor authentication.
            </h3>

            <div class="mt-3">
                <p>
                    When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.
                </p>
            </div>

            <div v-if="twoFactorEnabled">
                <div v-if="qrCode">
                    <div class="mt-4">
                        <p class="has-text-weight-semibold">
                            Two factor authentication is now enabled. Scan the following QR code using your phone's authenticator application.
                        </p>
                    </div>

                    <div class="mt-4" v-html="qrCode" />
                </div>

                <div v-if="recoveryCodes.length > 0">
                    <div class="mt-4">
                        <p class="has-text-weight-semibold">
                            Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.
                        </p>
                    </div>

                    <div class="mt-4 px-4 py-4">
                        <div
                            v-for="code in recoveryCodes"
                            :key="code"
                        >
                            {{ code }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <div v-if="!twoFactorEnabled">
                    <biz-confirm-password @confirmed="enableTwoFactorAuthentication">
                        <biz-button
                            :class="{ 'opacity-25': enabling }"
                            :disabled="enabling"
                            class="is-primary"
                        >
                            Enable
                        </biz-button>
                    </biz-confirm-password>
                </div>

                <div v-else>
                    <biz-confirm-password @confirmed="regenerateRecoveryCodes">
                        <biz-button
                            v-if="recoveryCodes.length > 0"
                        >
                            Regenerate Recovery Codes
                        </biz-button>
                    </biz-confirm-password>

                    <biz-confirm-password @confirmed="showRecoveryCodes">
                        <biz-button
                            v-if="recoveryCodes.length === 0"
                        >
                            Show Recovery Codes
                        </biz-button>
                    </biz-confirm-password>

                    <biz-confirm-password @confirmed="disableTwoFactorAuthentication">
                        <biz-button
                            class="is-danger ml-2"
                            :class="{ 'opacity-25': disabling }"
                            :disabled="disabling"
                        >
                            Disable
                        </biz-button>
                    </biz-confirm-password>
                </div>
            </div>
        </template>
    </form-action-section>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizConfirmPassword from '@/Biz/ConfirmPassword';
    import FormActionSection from '@/Frontend/ActionSection';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import { oops as oopsAlert } from '@/Libs/alert';

    export default {
        components: {
            BizButton,
            BizConfirmPassword,
            FormActionSection,
        },

        mixins: [
            MixinHasLoader,
        ],

        data() {
            return {
                enabling: false,
                disabling: false,

                qrCode: null,
                recoveryCodes: [],
            }
        },

        computed: {
            twoFactorEnabled() {
                return ! this.enabling && this.$page.props.user.two_factor_enabled
            }
        },

        methods: {
            enableTwoFactorAuthentication() {
                this.enabling = true;
                this.onStartLoadingOverlay();

                this.$inertia.post('/user/two-factor-authentication', {}, {
                    preserveScroll: true,
                    onSuccess: () => Promise.all([
                        this.showQrCode(),
                        this.showRecoveryCodes(),
                    ]),
                    onError: () => {
                        oopsAlert();
                    },
                    onFinish: () => {
                        this.enabling = false;
                        this.onEndLoadingOverlay();
                    },
                })
            },

            showQrCode() {
                let self = this;

                return axios.get('/user/two-factor-qr-code')
                    .then(response => {
                        self.qrCode = response.data.svg
                    })
                    .catch(() => {
                        oopsAlert();
                    });
            },

            showRecoveryCodes() {
                let self = this;

                return axios.get('/user/two-factor-recovery-codes')
                    .then(response => {
                        self.recoveryCodes = response.data
                    })
                    .catch(() => {
                        oopsAlert();
                    });
            },

            regenerateRecoveryCodes() {
                let self = this;

                axios.post('/user/two-factor-recovery-codes')
                    .then(response => {
                        self.showRecoveryCodes()
                    })
                    .catch(() => {
                        oopsAlert();
                    });
            },

            disableTwoFactorAuthentication() {
                this.disabling = true;
                this.onStartLoadingOverlay();

                this.$inertia.delete('/user/two-factor-authentication', {
                    preserveScroll: true,
                    onError: () => {
                        oopsAlert();
                    },
                    onFinish: () => {
                        this.disabling = false;
                        this.onEndLoadingOverlay();
                    },
                })
            },
        },
    }
</script>
