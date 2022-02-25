<template>
    <app-layout>
        <template #header>
            Verify Email
        </template>
        <div class="box">
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

                    <form
                        method="POST"
                        @submit.prevent="logout"
                        :action="route('logout')"
                        ref="logout"
                    >
                        <input type="hidden" name="_token" :value="csrfToken">

                        <div class="mt-4 flex items-center justify-between">
                            <biz-button
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                class="is-primary"
                            >
                                Resend Verification Email
                            </biz-button>

                            <biz-button
                                as="button"
                                class="button ml-3"
                            >
                                Log Out
                            </biz-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizLink from '@/Biz/Link';

    export default {
        components: {
            AppLayout,
            BizButton,
            BizLink,
        },

        props: {
            status: String
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
            logout() {
                this.$refs.logout.submit();
            },
        },
    }
</script>
