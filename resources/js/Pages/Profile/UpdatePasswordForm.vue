<template>
    <form-section @submitted="updatePassword">
        <template #title>
            Password
        </template>

        <template #form>
            <biz-form-password
                ref="current_password"
                v-model="form.current_password"
                autocomplete="current-password"
                label="Current Password"
                placeholder="Enter your password"
                wrapper-class="mb-5"
                :message="form.errors.current_password"
                :required="true"
            />

            <biz-form-password
                ref="password"
                v-model="form.password"
                autocomplete="new-password"
                label="New Password"
                placeholder="Enter your password"
                wrapper-class="mb-5"
                :message="form.errors.password"
                :required="true"
            />
        </template>

        <template #actions>
            <div class="field mb-5">
                <biz-button
                    class="is-medium is-primary"
                    :disabled="form.processing"
                >
                    <span class="has-text-weight-bold">Update password</span>
                </biz-button>
            </div>
        </template>
    </form-section>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButton from '@/Biz/Button.vue';
    import BizFormPassword from '@/Biz/Form/Password.vue';
    import FormSection from '@/Frontend/FormSection.vue';
    import { oops as oopsAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';

    export default {
        components: {
            BizButton,
            BizFormPassword,
            FormSection,
        },

        mixins: [
            MixinHasLoader,
        ],

        setup() {
            return {
                form: useForm({
                    current_password: '',
                    password: '',
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
                    },
                    onError: () => {
                        if (this.form.errors.password) {
                            this.form.reset('password');
                            this.$refs.password.$refs.input.focus();
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
