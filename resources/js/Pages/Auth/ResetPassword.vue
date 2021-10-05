<template>
    <section class="hero is-fullheight">
        <div class="hero-body">
            <div class="container has-text-centered">
                <div class="columns">
                    <div class="column is-two-fifths has-text-left">
                        <div class="card">
                            <div class="card-image">
                                <figure class="image is-3by4">
                                    <img src="https://dummyimage.com/550x715/e5e5e5/ffffff.jpg">
                                </figure>
                            </div>
                        </div>
                    </div>
                    <div class="column is-three-fifths has-text-left">
                        <div class="level">
                            <div class="level-left">
                                <div class="level-item">
                                    <sdb-link :href="route('login')">
                                        <span class="icon"><i class="fas fa-arrow-left"></i></span>
                                        <span>Back</span>
                                    </sdb-link>
                                </div>
                            </div>
                            <div class="level-right">
                                <div class="level-item">
                                </div>
                            </div>
                        </div>
                        <section class="section">
                            <div class="columns">
                                <div class="column is-9 is-offset-1">
                                    <div class="mb-4">
                                        <h1 class="title">
                                            Reset Password
                                        </h1>
                                        <h2 class="subtitle">
                                            <span>Lorem ipsum dolor sit amet.</span>
                                        </h2>
                                    </div>

                                    <div v-if="status" class="">
                                        {{ status }}
                                    </div>

                                    <jet-validation-errors class="mb-4" />

                                    <form @submit.prevent="submit">
                                        <div>
                                            <jet-label for="email" value="Email" />
                                            <jet-input id="email" type="email" class="has-background-light cursor-not-allowed" v-model="form.email" required readonly />
                                        </div>

                                        <div class="mt-4">
                                            <jet-label for="password" value="Password" />
                                            <jet-input id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="new-password" />
                                        </div>

                                        <div class="mt-4">
                                            <jet-label for="password_confirmation" value="Confirm Password" />
                                            <jet-input id="password_confirmation" type="password" class="mt-1 block w-full" v-model="form.password_confirmation" required autocomplete="new-password" />
                                        </div>

                                        <div class="mt-4">
                                            <jet-button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                                Reset Password
                                            </jet-button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
    import JetAuthenticationCard from '@/Jetstream/AuthenticationCard'
    import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo'
    import JetButton from '@/Jetstream/Button'
    import JetInput from '@/Jetstream/Input'
    import JetLabel from '@/Jetstream/Label'
    import JetValidationErrors from '@/Jetstream/ValidationErrors'
    import SdbLink from '@/Sdb/Link'

    export default {
        components: {
            JetAuthenticationCard,
            JetAuthenticationCardLogo,
            JetButton,
            JetInput,
            JetLabel,
            JetValidationErrors,
            SdbLink
        },

        props: {
            email: String,
            token: String,
        },

        data() {
            return {
                form: this.$inertia.form({
                    token: this.token,
                    email: this.email,
                    password: '',
                    password_confirmation: '',
                    processing: true,
                })
            }
        },

        methods: {
            submit() {
                this.form.post(this.route('password.update'), {
                    onFinish: () => this.form.reset('password', 'password_confirmation'),
                })
            }
        }
    }
</script>

<style scoped>
    .cursor-not-allowed {
        cursor:not-allowed;
    }
</style>
