<template>
    <sdb-action-section>
        <template #title>
            Two Factor Authentication
        </template>

        <template #description>
            Add additional security to your account using two factor authentication.
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
                    <sdb-confirm-password @confirmed="enableTwoFactorAuthentication">
                        <sdb-button
                            :class="{ 'opacity-25': enabling }"
                            :disabled="enabling"
                            class="is-primary"
                        >
                            Enable
                        </sdb-button>
                    </sdb-confirm-password>
                </div>

                <div v-else>
                    <sdb-confirm-password @confirmed="regenerateRecoveryCodes">
                        <sdb-button
                            v-if="recoveryCodes.length > 0"
                        >
                            Regenerate Recovery Codes
                        </sdb-button>
                    </sdb-confirm-password>

                    <sdb-confirm-password @confirmed="showRecoveryCodes">
                        <sdb-button
                            v-if="recoveryCodes.length === 0"
                        >
                            Show Recovery Codes
                        </sdb-button>
                    </sdb-confirm-password>

                    <sdb-confirm-password @confirmed="disableTwoFactorAuthentication">
                        <sdb-button
                            class="is-danger ml-2"
                            :class="{ 'opacity-25': disabling }"
                            :disabled="disabling"
                        >
                            Disable
                        </sdb-button>
                    </sdb-confirm-password>
                </div>
            </div>
        </template>
    </sdb-action-section>
</template>

<script>
    import SdbActionSection from '@/Sdb/ActionSection';
    import SdbConfirmPassword from '@/Sdb/ConfirmPassword';
    import SdbButton from '@/Sdb/Button';

    export default {
        components: {
            SdbActionSection,
            SdbConfirmPassword,
            SdbButton,
        },

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
                this.enabling = true

                this.$inertia.post('/user/two-factor-authentication', {}, {
                    preserveScroll: true,
                    onSuccess: () => Promise.all([
                        this.showQrCode(),
                        this.showRecoveryCodes(),
                    ]),
                    onFinish: () => (this.enabling = false),
                })
            },

            showQrCode() {
                return axios.get('/user/two-factor-qr-code')
                    .then(response => {
                        this.qrCode = response.data.svg
                    });
            },

            showRecoveryCodes() {
                return axios.get('/user/two-factor-recovery-codes')
                    .then(response => {
                        this.recoveryCodes = response.data
                    });
            },

            regenerateRecoveryCodes() {
                axios.post('/user/two-factor-recovery-codes')
                    .then(response => {
                        this.showRecoveryCodes()
                    });
            },

            disableTwoFactorAuthentication() {
                this.disabling = true

                this.$inertia.delete('/user/two-factor-authentication', {
                    preserveScroll: true,
                    onSuccess: () => (this.disabling = false),
                })
            },
        },
    }
</script>
