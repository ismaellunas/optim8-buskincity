<template>
    <biz-action-section>
        <template #title>
            Connected Accounts
        </template>

        <template #description>
            Manage and remove your connected accounts.
        </template>

        <template #content>
            <h3 v-if="$page.props.socialstream.connectedAccounts.length === 0">
                You have no connected accounts.
            </h3>
            <h3 v-else>
                Your connected accounts.
            </h3>

            <p>
                You are free to connect any social accounts to your profile and may remove any connected accounts at any time. If you feel any of your connected accounts have been compromised, you should disconnect them immediately and change your password.
            </p>

            <div class="mt-5">
                <div
                    v-for="(provider) in $page.props.socialstream.providers"
                    :key="provider"
                >
                    <connected-account
                        :provider="provider"
                        :created-at="hasAccountForProvider(provider) ? getAccountForProvider(provider).created_at : null"
                    >
                        <template #action>
                            <template v-if="hasAccountForProvider(provider)">
                                <div class="flex is-justify-content-center my-2">
                                    <biz-button
                                        v-if="$page.props.jetstream.managesProfilePhotos && getAccountForProvider(provider).avatar_path"
                                        class="cursor-pointer focus:outline-none"
                                        @click="setProfilePhoto(getAccountForProvider(provider).id)"
                                    >
                                        Use Avatar as Profile Photo
                                    </biz-button>

                                    <biz-button
                                        v-if="$page.props.socialstream.connectedAccounts.length > 1 || $page.props.socialstream.hasPassword"
                                        class="is-danger"
                                        @click="confirmRemove(getAccountForProvider(provider).id)"
                                    >
                                        Remove
                                    </biz-button>
                                </div>
                            </template>

                            <template v-else>
                                <action-link :href="route('oauth.redirect', { provider })">
                                    Connect
                                </action-link>
                            </template>
                        </template>
                    </connected-account>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <biz-modal-card
                v-if="isModalOpen"
                @close="closeModal()"
            >
                <template #header>
                    <p class="modal-card-title has-text-weight-bold">
                        Remove Connected Account
                    </p>
                    <button
                        class="delete"
                        aria-label="close"
                        @click="closeModal()"
                    />
                </template>

                <template #default>
                    Please confirm your removal of this account - this action cannot be undone.
                </template>

                <template #footer>
                    <biz-button
                        class="is-dark"
                        @click="closeModal()"
                    >
                        Nevermind
                    </biz-button>

                    <biz-button
                        class="is-primary ml-2"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="removeConnectedAccount(accountId)"
                    >
                        Remove Connected Account
                    </biz-button>
                </template>
            </biz-modal-card>
        </template>
    </biz-action-section>
</template>

<script>
    import ActionLink from '@/Socialstream/ActionLink';
    import ConnectedAccount from '@/Socialstream/ConnectedAccount';
    import MixinHasModal from '@/Mixins/HasModal';
    import BizActionSection from '@/Biz/ActionSection';
    import BizButton from '@/Biz/Button';
    import BizModalCard from '@/Biz/ModalCard';

    export default {
        components: {
            ActionLink,
            ConnectedAccount,
            BizActionSection,
            BizButton,
            BizModalCard,
        },

        mixins: [
            MixinHasModal,
        ],

        data() {
            return {
                accountId: null,

                form: this.$inertia.form({
                    '_method': 'DELETE',
                }, {
                    bag: 'removeConnectedAccount'
                })
            };
        },

        methods: {
            confirmRemove(id) {
                this.form.password = '';

                this.accountId = id;

                this.openModal();
            },

            hasAccountForProvider(provider) {
                return this.$page.props.socialstream.connectedAccounts.filter(account => account.provider === provider).length > 0;
            },

            getAccountForProvider(provider) {
                if (this.hasAccountForProvider(provider)) {
                    return this.$page.props.socialstream.connectedAccounts.filter(account => account.provider === provider).shift();
                }

                return null;
            },

            setProfilePhoto(id) {
                this.form.put(route('user-profile-photo.set', {id}), {
                    preserveScroll: true,
                });
            },

            removeConnectedAccount(id) {
                this.form.post(route('connected-accounts.destroy', {id}), {
                    preserveScroll: true,
                    onSuccess: () => (this.closeModal()),
                });
            },
        }
    }
</script>
