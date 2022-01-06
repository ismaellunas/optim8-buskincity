<template>
    <span>
        <span @click="startConfirmingPassword">
            <slot />
        </span>

        <sdb-modal-card
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
                    <sdb-form-password
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
                <sdb-button @click="closeModal">
                    Cancel
                </sdb-button>

                <sdb-button
                    class="is-primary"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="confirmPassword"
                >
                    {{ button }}
                </sdb-button>
            </template>
        </sdb-modal-card>
    </span>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import SdbButton from '@/Sdb/Button';
    import SdbFormPassword from '@/Sdb/Form/Password';
    import SdbModalCard from '@/Sdb/ModalCard';

    export default {
        name: 'SdbConfirmPassword',

        components: {
            SdbButton,
            SdbFormPassword,
            SdbModalCard,
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
