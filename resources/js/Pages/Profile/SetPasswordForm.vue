<template>
    <biz-form-section @submitted="setPassword">
        <template #title>
            Set Password
        </template>

        <template #description>
            Ensure your account is using a long, random password to stay secure.
        </template>

        <template #form>
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
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizActionMessage from '@/Biz/ActionMessage';
    import BizButton from '@/Biz/Button';
    import BizFormPassword from '@/Biz/Form/Password';
    import BizFormSection from '@/Biz/FormSection';
    import { oops as oopsAlert, confirmDelete, success as successAlert } from '@/Libs/alert';

    export default {
        components: {
            BizActionMessage,
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
                    password: '',
                    password_confirmation: '',
                }),
            }
        },

        methods: {
            setPassword() {
                this.onStartLoadingOverlay();

                this.form.put(route('user-password.set'), {
                    errorBag: 'setPassword',
                    preserveScroll: true,
                    onSuccess: () => {
                        this.form.reset();

                        successAlert('Saved');
                    },
                    onError: () => {
                        if (this.form.errors.password) {
                            this.form.reset('password', 'password_confirmation')
                            this.$refs.password.focus()
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
