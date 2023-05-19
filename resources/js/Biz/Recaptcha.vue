<template>
    <div>
        <biz-flash-failed
            class="mb-2"
            :flash="$page.props.flash"
        />

        <span
            v-if="isRecaptchaError"
            class="help has-text-danger"
        >
            Please check the reCAPTCHA!
        </span>
    </div>
</template>

<script>
    import BizFlashFailed from '@/Biz/FlashFailed.vue';

    export default {
        name: 'BizRecaptcha',

        components: {
            BizFlashFailed,
        },

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
                let recaptchaScript = document.createElement('script')

                recaptchaScript.setAttribute(
                    'src',
                    'https://www.google.com/recaptcha/api.js?render=' + this.siteKey
                );
                document.head.appendChild(recaptchaScript)
            }
        },

        methods: {
            execute() {
                const self = this;

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
            },
        },
    }
</script>