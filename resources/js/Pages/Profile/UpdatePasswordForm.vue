<template>
    <sdb-form-section @submitted="updatePassword">
        <template #title>
            Update Password
        </template>

        <template #description>
            Ensure your account is using a long, random password to stay secure.
        </template>

        <template #form>
            <sdb-form-password
                ref="current_password"
                v-model="form.current_password"
                autocomplete="current-password"
                label="Current Password"
                :message="form.errors.current_password"
                :required="true"
            />

            <sdb-form-password
                ref="password"
                v-model="form.password"
                autocomplete="new-password"
                label="New Password"
                :message="form.errors.password"
                :required="true"
            />

            <sdb-form-password
                ref="password"
                v-model="form.password_confirmation"
                autocomplete="new-password"
                label="Confirm Password"
                :message="form.errors.password_confirmation"
                :required="true"
            />
        </template>

        <template #actions>
            <sdb-action-message
                :is-active="form.recentlySuccessful"
                class="mr-3"
            >
                Saved.
            </sdb-action-message>

            <sdb-button
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                class="is-primary"
            >
                Save
            </sdb-button>
        </template>
    </sdb-form-section>
</template>

<script>
    import SdbActionMessage from '@/Sdb/ActionMessage';
    import SdbButton from '@/Sdb/Button';
    import SdbFormPassword from '@/Sdb/Form/Password';
    import SdbFormSection from '@/Sdb/FormSection';

    export default {
        components: {
            SdbActionMessage,
            SdbButton,
            SdbFormPassword,
            SdbFormSection,
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
