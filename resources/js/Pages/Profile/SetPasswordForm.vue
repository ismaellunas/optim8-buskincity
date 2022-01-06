<template>
    <sdb-form-section @submitted="setPassword">
        <template #title>
            Set Password
        </template>

        <template #description>
            Ensure your account is using a long, random password to stay secure.
        </template>

        <template #form>
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
                    password: '',
                    password_confirmation: '',
                }),
            }
        },

        methods: {
            setPassword() {
                this.form.put(route('user-password.set'), {
                    errorBag: 'setPassword',
                    preserveScroll: true,
                    onSuccess: () => this.form.reset(),
                    onError: () => {
                        if (this.form.errors.password) {
                            this.form.reset('password', 'password_confirmation')
                            this.$refs.password.focus()
                        }
                    }
                })
            },
        },
    }
</script>
