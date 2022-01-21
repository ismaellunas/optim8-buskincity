<template>
    <biz-action-section>
        <template #title>
            Delete Account
        </template>

        <template #description>
            Permanently delete your account.
        </template>

        <template #content>
            <p>
                Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.
            </p>

            <div class="mt-5">
                <biz-button
                    class="is-danger"
                    @click="confirmUserDeletion()"
                >
                    Delete Account
                </biz-button>
            </div>

            <!-- Delete Account Confirmation Modal -->
            <biz-modal-card
                v-if="isModalOpen"
                @close="closeModal()"
            >
                <template #header>
                    <p class="modal-card-title has-text-weight-bold">
                        Delete Account
                    </p>
                    <button
                        class="delete"
                        aria-label="close"
                        @click="closeModal()"
                    />
                </template>

                <template #default>
                    <p>
                        Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
                    </p>

                    <div class="mt-4">
                        <biz-form-password
                            ref="password"
                            v-model="form.password"
                            placeholder="Password"
                            :message="error('password')"
                            :required="true"
                            @keyup.enter="deleteUser"
                        />
                    </div>
                </template>

                <template #footer>
                    <biz-button @click="closeModal()">
                        Cancel
                    </biz-button>

                    <biz-button
                        class="is-danger ml-2"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Delete Account
                    </biz-button>
                </template>
            </biz-modal-card>
        </template>
    </biz-action-section>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizActionSection from '@/Biz/ActionSection';
    import BizButton from '@/Biz/Button';
    import BizFormPassword from '@/Biz/Form/Password';
    import BizModalCard from '@/Biz/ModalCard';

    export default {
        components: {
            BizActionSection,
            BizButton,
            BizFormPassword,
            BizModalCard,
        },

        mixins: [
            MixinHasModal,
            MixinHasPageErrors,
        ],

        data() {
            return {
                form: this.$inertia.form({
                    password: '',
                })
            }
        },

        methods: {
            confirmUserDeletion() {
                this.openModal();

                setTimeout(() => this.$refs.password.$refs.input.focus(), 250)
            },

            deleteUser() {
                this.form.delete(route('current-user.destroy'), {
                    preserveScroll: true,
                    onSuccess: () => this.closeModal(),
                    onError: () => this.$refs.password.$refs.input.focus(),
                    onFinish: () => this.form.reset(),
                })
            },

            closeModal() {
                this.isModalOpen = false

                this.form.reset()
            },
        },
    }
</script>
