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
                recaptchaId: 'biz-recaptcha-tag',
            }
        },

        computed: {
            isRecaptchaAvailable() {
                return !!this.siteKey
                    && !this.isRecaptchaError;
            },
        },

        methods: {
            loadScript(src, async = true, type = "text/javascript") {
                return new Promise((resolve, reject) => {
                    try {
                        const tag = document.createElement("script");
                        const container = document.head || document.body;

                        tag.type = type;
                        tag.async = async;
                        tag.id =  this.recaptchaId;
                        tag.src = src;

                        tag.addEventListener("load", () => {
                            resolve({ loaded: true, error: false });
                        });

                        tag.addEventListener("error", () => {
                            reject({
                                loaded: false,
                                error: true,
                                message: `Failed to load script with src ${src}`,
                            });
                        });

                        container.appendChild(tag);
                    } catch (error) {
                        reject(error);
                    }
                });
            },

            hasRegisteredScript() {
                return !!document.head.querySelector('#'+this.recaptchaId);
            },

            execute() {
                return new Promise(async (resolve, reject) => {
                    if (this.isRecaptchaAvailable) {
                        if (! this.recaptchaScript && !this.hasRegisteredScript()) {
                            await this.loadScript(
                                'https://www.google.com/recaptcha/api.js?render=' + this.siteKey
                            );
                        }

                        if (typeof grecaptcha !== 'undefined') {
                            grecaptcha.ready(() => {
                                try {
                                    grecaptcha.execute(this.siteKey, {
                                        action: this.action
                                    })
                                        .then((response) => {
                                            resolve(response);

                                            this.$emit('on-verify', response);
                                        });
                                } catch (error) {
                                    this.isRecaptchaError = true;

                                    reject(error);

                                    this.$emit('on-verify');
                                }
                            });

                        } else {
                            reject({error: true});

                            this.$emit('on-verify');
                        }
                    } else {
                        resolve({error: false});

                        this.$emit('on-verify');
                    }
                });
            },
        },
    };
</script>
