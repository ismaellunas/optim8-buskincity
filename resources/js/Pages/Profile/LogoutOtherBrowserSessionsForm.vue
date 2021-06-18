<template>
    <jet-action-section>
        <template #title>
            Browser Sessions
        </template>

        <template #description>
            Manage and log out your active sessions on other browsers and devices.
        </template>

        <template #content>
            <div class="">
                If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.
            </div>

            <!-- Other Browser Sessions -->
            <div class="mt-5" v-if="sessions.length > 0">
                <table class="table is-bordered is-striped is-hoverable is-fullwidth">
                    <tbody>
                        <tr v-for="(session, i) in sessions" :key="i">
                            <td class="has-text-centered">
                                <i class="fas fa-desktop fa-3x" v-if="session.agent.is_desktop"></i>
                                <i class="fas fa-mobile-alt fa-3x" v-else></i>
                            </td>
                            <td>
                                <div class="">
                                    {{ session.agent.platform }} - {{ session.agent.browser }}
                                </div>

                                <div>
                                    <div class="">
                                        {{ session.ip_address }},
                                        <span class="has-text-weight-semibold" v-if="session.is_current_device">This device</span>
                                        <span v-else>Last active {{ session.last_active }}</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex mt-5">
                <jet-button @click="confirmLogout">
                    Log Out Other Browser Sessions
                </jet-button>
            </div>

            <!-- Log Out Other Devices Confirmation Modal -->
            <jet-dialog-modal :show="confirmingLogout" @close="closeModal">
                <template #title>
                    Log Out Other Browser Sessions
                </template>

                <template #content>
                    Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.

                    <div class="mt-4">
                        <jet-input type="password" class="mt-1 block w-3/4" placeholder="Password"
                                    ref="password"
                                    v-model="form.password"
                                    @keyup.enter="logoutOtherBrowserSessions" />

                        <jet-input-error :message="form.errors.password" class="mt-2" />
                    </div>
                </template>

                <template #footer>
                    <jet-secondary-button @click="closeModal">
                        Cancel
                    </jet-secondary-button>

                    <jet-button class="ml-2" @click="logoutOtherBrowserSessions" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Log Out Other Browser Sessions
                    </jet-button>
                </template>
            </jet-dialog-modal>
        </template>
    </jet-action-section>
</template>

<script>
    import JetActionMessage from '@/Jetstream/ActionMessage'
    import JetActionSection from '@/Jetstream/ActionSection'
    import JetButton from '@/Jetstream/Button'
    import JetDialogModal from '@/Jetstream/DialogModal'
    import JetInput from '@/Jetstream/Input'
    import JetInputError from '@/Jetstream/InputError'
    import JetSecondaryButton from '@/Jetstream/SecondaryButton'

    export default {
        props: ['sessions'],

        components: {
            JetActionMessage,
            JetActionSection,
            JetButton,
            JetDialogModal,
            JetInput,
            JetInputError,
            JetSecondaryButton,
        },

        data() {
            return {
                confirmingLogout: false,

                form: this.$inertia.form({
                    password: '',
                })
            }
        },

        methods: {
            confirmLogout() {
                this.confirmingLogout = true

                setTimeout(() => this.$refs.password.focus(), 250)
            },

            logoutOtherBrowserSessions() {
                this.form.delete(route('other-browser-sessions.destroy'), {
                    preserveScroll: true,
                    onSuccess: () => this.closeModal(),
                    onError: () => this.$refs.password.focus(),
                    onFinish: () => this.form.reset(),
                })
            },

            closeModal() {
                this.confirmingLogout = false

                this.form.reset()
            },
        },
    }
</script>
