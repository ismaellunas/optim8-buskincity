<template>
    <action-section>
        <template #title>
            Connected Accounts
        </template>

        <template #content>
            <div class="buttons">
                <template
                    v-for="(provider) in socialiteDrivers"
                    :key="provider"
                >
                    <template v-if="hasAccountForProvider(provider)">
                        <biz-button
                            v-if="$page.props.socialstream.connectedAccounts.length > 1 || $page.props.socialstream.hasPassword"
                            class="is-medium mr-4"
                            @click="confirmRemove(getAccountForProvider(provider).id)"
                        >
                            <connected-account-icon :provider="provider" />

                            <span class="has-text-weight-bold">
                                {{ providerTitle(provider) }}
                            </span>
                            <span> - Connected</span>
                        </biz-button>
                    </template>

                    <template v-else>
                        <a
                            :href="route('oauth.redirect', { provider })"
                            class="button is-medium mr-4"
                        >
                            <connected-account-icon :provider="provider" />

                            <span class="has-text-weight-bold">
                                {{ providerTitle(provider) }}
                            </span>
                        </a>
                    </template>
                </template>
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
                        @click="closeModal()"
                    >
                        Cancel
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
    </action-section>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import ActionSection from '@/Frontend/ActionSection';
    import BizButton from '@/Biz/Button';
    import BizModalCard from '@/Biz/ModalCard';
    import ConnectedAccountIcon from '@/Socialstream/ConnectedAccountIcon';

    export default {
        components: {
            ActionSection,
            BizButton,
            BizModalCard,
            ConnectedAccountIcon,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
        ],

        inject: [
            'socialiteDrivers',
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

            removeConnectedAccount(id) {
                this.onStartLoadingOverlay();

                this.form.post(route('connected-accounts.destroy', {id}), {
                    preserveScroll: true,
                    onSuccess: () => (this.closeModal()),
                    onFinish: () => (this.onEndLoadingOverlay()),
                });
            },

            providerTitle(provider) {
                return provider.charAt(0).toUpperCase() + provider.slice(1);
            }
        }
    }
</script>
