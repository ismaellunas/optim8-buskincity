export default {
    props: {
        recaptchaSiteKey: { type: [String, null], default: null },
    },

    data() {
        return {
            isRecaptchaError: false,
        }
    },

    computed: {
        isRecaptchaAvailable() {
            return !!this.recaptchaSiteKey
                && !this.isRecaptchaError;
        },
    },

    methods: {
        recaptchaExecute() {
            this.$refs.vueRecaptcha.execute();
        },

        recaptchaExpired() {
            if (
                this.$refs.vueRecaptcha
                && this.isRecaptchaAvailable
            ) {
                this.$refs.vueRecaptcha.reset();
            }
        },

        recaptchaFailed() {
            this.isRecaptchaError = true;
        },

        recaptchaVerify(response) { },
    },
};
