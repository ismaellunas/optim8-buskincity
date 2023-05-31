<template>
    <span
        v-if="isRecaptchaError"
        class="help has-text-danger"
    >
        Please check the reCAPTCHA!
    </span>
</template>

<script>
    export default {
        name: 'BizRecaptcha',

        props: {
            siteKey: { type: [String, null], default: null },
            action: { type: String, default: 'submit' },
        },

        emits: [
            'on-verify'
        ],

        data() {
            return {
                isRecaptchaError: false,
                recaptchaScript: null,
            }
        },

        computed: {
            isRecaptchaAvailable() {
                return !!this.siteKey
                    && !this.isRecaptchaError;
            },
        },

        mounted() {
            if (this.isRecaptchaAvailable) {
                this.recaptchaScript = document.createElement('script')

                this.recaptchaScript.setAttribute(
                    'src',
                    'https://www.google.com/recaptcha/api.js?render=' + this.siteKey
                );
                document.head.appendChild(this.recaptchaScript);
            }
        },

        unmounted() {
            if (this.isRecaptchaAvailable && this.recaptchaScript) {
                document.head.removeChild(this.recaptchaScript);
            }
        },

        methods: {
            execute() {
                const self = this;

                if (self.isRecaptchaAvailable) {
                    grecaptcha.ready(function() {
                        try {
                            grecaptcha.execute(self.siteKey, {
                                action: self.action
                            })
                                .then((response) => {
                                    self.$emit('on-verify', response);
                                });
                        } catch (error) {
                            self.isRecaptchaError = true;
                            self.$emit('on-verify');
                        }
                    });
                } else {
                    self.$emit('on-verify');
                }
            },
        },
    }
</script>