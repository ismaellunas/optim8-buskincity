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
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButton from '@/Biz/Button';
    import BizFormPassword from '@/Biz/Form/Password';
    import BizFormSection from '@/Biz/FormSection';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';

    export default {
        components: {
            BizButton,
            BizFormPassword,
            BizFormSection,
        },

        mixins: [
            MixinHasLoader,
        ],

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
                this.onStartLoadingOverlay();

                this.form.put(route('user-password.update'), {
                    errorBag: 'updatePassword',
                    preserveScroll: true,
                    onSuccess: () => {
                        this.form.reset();

                        successAlert('Saved');
                    },
                    onError: () => {
                        if (this.form.errors.password) {
                            this.form.reset('password', 'password_confirmation')
                            this.$refs.password.$refs.input.focus()
                        }

                        if (this.form.errors.current_password) {
                            this.form.reset('current_password')
                            this.$refs.current_password.$refs.input.focus()
                        }

                        oopsAlert();
                    },
                    onFinish: () => {
                        this.onEndLoadingOverlay();
                    },
                })
            },
        },
    }
</script>
