<template>
    <div>
        <template
            v-if="isRecaptchaAvailable"
        >
            <vue-recaptcha
                ref="vueRecaptcha"
                :sitekey="siteKey"
                size="invisible"
                theme="light"
                @expired="onExpired"
                @error="onError"
                @verify="onVerify"
            />
        </template>

        <span
            v-if="isRecaptchaError"
            class="help has-text-danger"
        >
            Please check the reCAPTCHA!
        </span>
    </div>
</template>

<script>
    import { VueRecaptcha } from 'vue-recaptcha';

    export default {
        name: 'BizRecaptcha',

        components: {
            VueRecaptcha,
        },

        props: {
            siteKey: { type: [String, null], default: null },
        },

        emits: [
            'on-verify'
        ],

        data() {
            return {
                isRecaptchaError: false,
            }
        },

        computed: {
            isRecaptchaAvailable() {
                return !!this.siteKey
                    && !this.isRecaptchaError;
            },

            isRecaptchaLoaded() {
                return !!this.$refs.vueRecaptcha;
            },
        },

        methods: {
            execute() {
                if (this.isRecaptchaLoaded) {
                    this.$refs.vueRecaptcha.execute();
                } else {
                    this.$emit('on-verify');
                }
            },

            onExpired() {
                if (
                    this.isRecaptchaLoaded
                    && this.isRecaptchaAvailable
                ) {
                    this.$refs.vueRecaptcha.reset();
                }
            },

            onError() {
                this.isRecaptchaError = true;
            },

            onVerify(response) {
                this.$emit('on-verify', response);
            },
        },
    }
</script>