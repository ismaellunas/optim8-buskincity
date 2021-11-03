<template>
    <jet-form-section @submitted="updatePassword">
        <template #title>
            Update Password
        </template>

        <template #description>
            Ensure your account is using a long, random password to stay secure.
        </template>

        <template #form>
            <div class="col-span-6 sm:col-span-4 mb-5">
                <sdb-form-password
                    ref="current_password"
                    v-model="form.current_password"
                    autocomplete="current-password"
                    label="Current Password"
                    :message="form.errors.current_password"
                    :required="true"
                ></sdb-form-password>
            </div>

            <div class="col-span-6 sm:col-span-4 mb-5">
                <sdb-form-password
                    ref="password"
                    v-model="form.password"
                    autocomplete="new-password"
                    label="New Password"
                    :message="form.errors.password"
                    :required="true"
                ></sdb-form-password>
            </div>

            <div class="col-span-6 sm:col-span-4 mb-5">
                <sdb-form-password
                    ref="password"
                    v-model="form.password_confirmation"
                    autocomplete="new-password"
                    label="Confirm Password"
                    :message="form.errors.password_confirmation"
                    :required="true"
                ></sdb-form-password>
            </div>
        </template>

        <template #actions>
            <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                Saved.
            </jet-action-message>

            <jet-button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </jet-button>
        </template>
    </jet-form-section>
</template>

<script>
    import JetActionMessage from '@/Jetstream/ActionMessage'
    import JetButton from '@/Jetstream/Button'
    import JetFormSection from '@/Jetstream/FormSection'
    import SdbFormPassword from '@/Sdb/Form/Password';

    export default {
        components: {
            JetActionMessage,
            JetButton,
            JetFormSection,
            SdbFormPassword,
        },

        data() {
            return {
                form: this.$inertia.form({
                    current_password: '',
                    password: '',
                    password_confirmation: '',
                }),
            }
        },

        methods: {
            updatePassword() {
                this.form.put(route('user-password.update'), {
                    errorBag: 'updatePassword',
                    preserveScroll: true,
                    onSuccess: () => this.form.reset(),
                    onError: () => {
                        if (this.form.errors.password) {
                            this.form.reset('password', 'password_confirmation')
                            this.$refs.password.$refs.input.focus()
                        }

                        if (this.form.errors.current_password) {
                            this.form.reset('current_password')
                            this.$refs.current_password.$refs.input.focus()
                        }
                    }
                })
            },
        },
    }
</script>
