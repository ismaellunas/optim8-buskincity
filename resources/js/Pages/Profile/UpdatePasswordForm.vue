<template>
    <biz-form-section @submitted="updatePassword">
        <template #title>
            Update Password
        </template>

        <template #description>
            Ensure your account is using a long, random password to stay secure.
        </template>

        <template #form>
            <biz-form-password
                ref="current_password"
                v-model="form.current_password"
                autocomplete="current-password"
                label="Current Password"
                :message="form.errors.current_password"
                :required="true"
            />

            <biz-form-password
                ref="password"
                v-model="form.password"
                autocomplete="new-password"
                label="New Password"
                :message="form.errors.password"
                :required="true"
            />

            <biz-form-password
                ref="password"
                v-model="form.password_confirmation"
                autocomplete="new-password"
                label="Confirm Password"
                :message="form.errors.password_confirmation"
                :required="true"
            />
        </template>

        <template #actions>
            <biz-action-message
                :is-active="form.recentlySuccessful"
                class="mr-3"
            >
                Saved.
            </biz-action-message>

            <biz-button
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                class="is-primary"
            >
                Save
            </biz-button>
        </template>
    </biz-form-section>
</template>

<script>
    import BizActionMessage from '@/Biz/ActionMessage';
    import BizButton from '@/Biz/Button';
    import BizFormPassword from '@/Biz/Form/Password';
    import BizFormSection from '@/Biz/FormSection';

    export default {
        components: {
            BizActionMessage,
            BizButton,
            BizFormPassword,
            BizFormSection,
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
