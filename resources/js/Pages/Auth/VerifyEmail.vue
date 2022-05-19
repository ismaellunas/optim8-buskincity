<template>
    <div class="box is-shadowless">
        <div class="columns">
            <div class="column">
                <div class="mb-4">
                    Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
                </div>

                <div
                    v-if="verificationLinkSent"
                    class="notification is-info"
                >
                    A new verification link has been sent to the email address you provided during registration.
                </div>

                <div class="mt-4 is-flex items-center justify-between">
                    <form @submit.prevent="submit">
                        <biz-button
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            class="is-primary"
                        >
                            Resend Verification Email
                        </biz-button>
                    </form>

                    <form
                        ref="logout"
                        method="POST"
                        :action="route('logout')"
                        @submit.prevent="logout"
                    >
                        <input
                            type="hidden"
                            name="_token"
                            :value="csrfToken"
                        >

                        <biz-button
                            class="button ml-3"
                        >
                            Log Out
                        </biz-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButton from '@/Biz/Button';

    export default {
        components: {
            BizButton,
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            status: { default: null, type: String }
        },

        data() {
            return {
                form: this.$inertia.form(),
                csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        },

        computed: {
            verificationLinkSent() {
                return this.status === 'verification-link-sent';
            }
        },

        methods: {
            submit() {
                this.form.post(this.route('verification.send'), {
                    onStart: () => this.onStartLoadingOverlay(),
                    onFinish: () => this.onEndLoadingOverlay(),
                })
            },

            logout() {
                if (route().current('admin*')) {
                    this.$inertia.post(route('logout'));
                } else {
                    this.$refs.logout.submit();
                }
            },
        },
    }
</script>
