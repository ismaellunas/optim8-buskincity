<template>
    <app-layout>
        <template v-slot:header>Verify Email</template>
        <div class="box">
            <div class="columns">
                <div class="column">
                    <div class="mb-4">
                        Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
                    </div>

                    <div class="notification is-info" v-if="verificationLinkSent">
                        A new verification link has been sent to the email address you provided during registration.
                    </div>

                    <form @submit.prevent="submit">
                        <div class="mt-4 flex items-center justify-between">
                            <biz-button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="is-primary">
                                Resend Verification Email
                            </biz-button>

                            <biz-link :href="route('logout')" method="post" as="button" class="button ml-3">Log Out</biz-link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout'
    import BizButton from '@/Biz/Button'
    import BizLink from '@/Biz/Link'

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
                form: this.$inertia.form()
            }
        },

        methods: {
            submit() {
                this.form.post(this.route('verification.send'))
            },
        },

        computed: {
            verificationLinkSent() {
                return this.status === 'verification-link-sent';
            }
        }
    }
</script>
