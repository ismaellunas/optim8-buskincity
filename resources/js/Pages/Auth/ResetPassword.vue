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
                                        <span class="icon">
                                            <i class="fas fa-arrow-left" />
                                        </span>
                                        <span>Back</span>
                                    </sdb-link>
                                </div>
                            </div>
                            <div class="level-right">
                                <div class="level-item" />
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

                                    <sdb-error-notifications
                                        :errors="$page.props.errors"
                                    />

                                    <form @submit.prevent="submit">
                                        <div>
                                            <sdb-form-input
                                                v-model="form.email"
                                                label="Email"
                                                required
                                                type="email"
                                                disabled
                                                :message="error('email')"
                                            />
                                        </div>

                                        <div class="mt-4">
                                            <sdb-form-password
                                                v-model="form.password"
                                                label="Password"
                                                :required="true"
                                            />
                                        </div>

                                        <div class="mt-4">
                                            <sdb-form-password
                                                v-model="form.password_confirmation"
                                                autocomplete="new-password"
                                                label="Confirm Password"
                                                :required="true"
                                            />
                                        </div>

                                        <div class="mt-4">
                                            <sdb-button
                                                class="button is-info"
                                                :disabled="form.processing"
                                            >
                                                Reset Password
                                            </sdb-button>
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
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import SdbButton from '@/Sdb/Button';
    import SdbErrorNotifications from '@/Sdb/ErrorNotifications';
    import SdbFormInput from '@/Sdb/Form/Input';
    import SdbFormPassword from '@/Sdb/Form/Password';
    import SdbLink from '@/Sdb/Link'

    export default {
        components: {
            SdbButton,
            SdbErrorNotifications,
            SdbFormInput,
            SdbFormPassword,
            SdbLink,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            email: {
                type: String,
                required: true,
            },
            token: {
                type: String,
                required: true,
            },
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