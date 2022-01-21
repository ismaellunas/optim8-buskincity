<template>
    <span>
        <span @click="startConfirmingPassword">
            <slot />
        </span>

        <biz-modal-card
            v-if="isModalOpen"
            @close="closeModal()"
        >
            <template #header>
                <p class="modal-card-title has-text-weight-bold">
                    {{ title }}
                </p>
                <button
                    class="delete"
                    aria-label="close"
                    @click="closeModal()"
                />
            </template>

            <template #default>
                {{ content }}

                <div class="mt-4">
                    <biz-form-password
                        ref="password"
                        v-model="form.password"
                        placeholder="Password"
                        :message="form.error"
                        :required="true"
                        @keyup.enter="confirmPassword"
                    />
                </div>
            </template>

            <template #footer>
                <biz-button @click="closeModal">
                    Cancel
                </biz-button>

                <biz-button
                    class="is-primary"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="confirmPassword"
                >
                    {{ button }}
                </biz-button>
            </template>
        </biz-modal-card>
    </span>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import BizButton from '@/Biz/Button';
    import BizFormPassword from '@/Biz/Form/Password';
    import BizModalCard from '@/Biz/ModalCard';

    export default {
        name: 'BizConfirmPassword',

        components: {
            BizButton,
            BizFormPassword,
            BizModalCard,
        },

        mixins: [
            MixinHasModal,
        ],

        props: {
            title: {
                type: String,
                default: 'Confirm Password',
            },
            content: {
                type: String,
                default: 'For your security, please confirm your password to continue.',
            },
            button: {
                type: String,
                default: 'Confirm',
            }
        },

        emits: ['confirmed'],

        data() {
            return {
                form: {
                    password: '',
                    error: '',
                },
            }
        },

        methods: {
            startConfirmingPassword() {
                axios.get(route('password.confirmation')).then(response => {
                    if (response.data.confirmed) {
                        this.$emit('confirmed');
                    } else {
                        this.openModal();

                        setTimeout(() => this.$refs.password.$refs.input.focus(), 250)
                    }
                })
            },

            confirmPassword() {
                this.form.processing = true;

                axios.post(route('password.confirm'), {
                    password: this.form.password,
                }).then(() => {
                    this.form.processing = false;
                    this.closeModal();
                    this.$nextTick(() => this.$emit('confirmed'));
                }).catch(error => {
                    this.form.processing = false;
                    this.form.error = error.response.data.errors.password[0];
                    this.$refs.password.$refs.input.focus();
                });
            },

            closeModal() {
                this.isModalOpen = false
                this.form.password = '';
                this.form.error = '';
            },
        }
    }
</script>
