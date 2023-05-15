<template>
    <form-section @submitted="setPassword">
        <template #title>
            Set Password
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
            <div class="field mb-5">
                <biz-button
                    class="is-medium is-primary"
                    :disabled="form.processing"
                >
                    <span class="has-text-weight-bold">Set Password</span>
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
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
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
                    password: '',
                    password_confirmation: '',
                }),
            };
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
                            this.$refs.password.$refs.input.focus();
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
