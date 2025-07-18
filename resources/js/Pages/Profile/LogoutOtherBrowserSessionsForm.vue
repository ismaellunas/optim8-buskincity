<template>
    <action-section>
        <template #title>
            Browser Sessions
        </template>

        <template #content>
            <p>
                Manage and log out your active sessions on other browsers and devices.
            </p>
            <p>
                If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.
            </p>

            <!-- Other Browser Sessions -->
            <div
                v-if="sessions.length > 0"
                class="mt-5"
            >
                <table class="table is-bordered is-striped is-hoverable is-fullwidth">
                    <tbody>
                        <tr
                            v-for="(session, i) in sessions"
                            :key="i"
                        >
                            <td class="has-text-centered">
                                <i
                                    v-if="session.agent.is_desktop"
                                    class="fas fa-desktop fa-3x"
                                />
                                <i
                                    v-else
                                    class="fas fa-mobile-alt fa-3x"
                                />
                            </td>
                            <td>
                                <div>
                                    {{ session.agent.platform }} - {{ session.agent.browser }}
                                </div>

                                <div>
                                    <div>
                                        {{ session.ip_address }},
                                        <span
                                            v-if="session.is_current_device"
                                            class="has-text-weight-semibold"
                                        >
                                            This device
                                        </span>
                                        <span v-else>
                                            Last active {{ session.last_active }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex mt-5">
                <biz-button
                    class="is-medium is-primary"
                    @click="confirmLogout"
                >
                    <span class="has-text-weight-bold">
                        Log Out Other Browser Sessions
                    </span>
                </biz-button>
            </div>

            <!-- Log Out Other Devices Confirmation Modal -->
            <biz-modal-card
                v-if="isModalOpen"
                @close="closeModal()"
            >
                <template #header>
                    <p class="modal-card-title has-text-weight-bold">
                        Log Out Other Browser Sessions
                    </p>
                    <button
                        class="delete"
                        aria-label="close"
                        @click="closeModal()"
                    />
                </template>

                <template #default>
                    <p>
                        Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.
                    </p>

                    <div class="mt-4">
                        <biz-form-password
                            ref="password"
                            v-model="form.password"
                            placeholder="Password"
                            :message="error('password')"
                            :required="true"
                            @keyup.enter="logoutOtherBrowserSessions"
                        />
                    </div>
                </template>

                <template #footer>
                    <biz-button
                        @click="closeModal()"
                    >
                        Cancel
                    </biz-button>

                    <biz-button
                        class="is-primary ml-2"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="logoutOtherBrowserSessions"
                    >
                        Log Out Other Browser Sessions
                    </biz-button>
                </template>
            </biz-modal-card>
        </template>
    </action-section>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import ActionSection from '@/Frontend/ActionSection.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizFormPassword from '@/Biz/Form/Password.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import { oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';

    export default {
        components: {
            ActionSection,
            BizButton,
            BizFormPassword,
            BizModalCard,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
            MixinHasPageErrors,
        ],

        props: {
            sessions: {
                type: Array,
                default:() => [],
            }
        },

        setup() {
            return {
                form: useForm({
                    password: '',
                })
            }
        },

        methods: {
            confirmLogout() {
                this.openModal()

                setTimeout(() => this.$refs.password.$refs.input.focus(), 250)
            },

            logoutOtherBrowserSessions() {
                this.onStartLoadingOverlay();

                this.form.delete(route('other-browser-sessions.destroy'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.closeModal();

                        successAlert('Done');
                    },
                    onError: () => {
                        this.$refs.password.$refs.input.focus();

                        oopsAlert();
                    },
                    onFinish: () => {
                        this.form.reset();

                        this.onEndLoadingOverlay();
                    },
                })
            },

            closeModal() {
                this.isModalOpen = false

                this.form.reset()
            },
        },
    }
</script>
