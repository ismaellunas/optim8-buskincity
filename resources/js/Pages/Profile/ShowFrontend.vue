<template>
    <div>
        <div class="columns is-multiline is-mobile">
            <update-profile-information-form
                v-if="$page.props.jetstream.canUpdateProfileInformation"
                class="column is-12-desktop is-12-tablet is-12-mobile"
                :user="$page.props.user"
                :language-options="supportedLanguageOptions"
                @after-update-profile="reSchema()"
            />

            <update-password-form
                v-if="$page.props.jetstream.canUpdatePassword && $page.props.socialstream.hasPassword"
                class="column is-12-desktop is-12-tablet is-12-mobile"
            />

            <set-password-form
                v-else-if="can.set_password && ! $page.props.socialstream.hasPassword"
                class="column is-12-desktop is-12-tablet is-12-mobile"
            />

            <biodata-form
                :key="biodataFormKey"
                class="column is-12-desktop is-12-tablet is-12-mobile"
                :user="$page.props.user"
            />

            <two-factor-authentication-form
                v-if="$page.props.jetstream.canManageTwoFactorAuthentication && $page.props.socialstream.hasPassword"
                class="column is-12-desktop is-12-tablet is-12-mobile"
            />

            <connected-accounts-form
                v-if="isConnectedAccountFormEnabled"
                class="column is-12-desktop is-12-tablet is-12-mobile"
            />

            <logout-other-browser-sessions-form
                v-if="$page.props.socialstream.hasPassword"
                :sessions="sessions"
                class="column is-12-desktop is-12-tablet is-12-mobile"
            />

            <div
                v-if="$page.props.jetstream.hasAccountDeletionFeatures && $page.props.socialstream.hasPassword"
                class="mb-5"
            >
                <delete-user-form class="mt-10 sm:mt-0" />
            </div>
        </div>
    </div>
</template>

<script>
    import Layout from '@/Layouts/User.vue';
    import BiodataForm from './BiodataForm.vue';
    import ConnectedAccountsForm from './ConnectedAccountsForm.vue';
    import DeleteUserForm from './DeleteUserForm.vue';
    import LogoutOtherBrowserSessionsForm from './LogoutOtherBrowserSessionsForm.vue';
    import SetPasswordForm from './SetPasswordForm.vue';
    import TwoFactorAuthenticationForm from './TwoFactorAuthenticationForm.vue';
    import UpdatePasswordForm from './UpdatePasswordForm.vue';
    import UpdateProfileInformationForm from './UpdateProfileInformationForm.vue';
    import { success, oops } from '@/Libs/alert';

    export default {
        name: 'ProfileShowFrontend',

        components: {
            BiodataForm,
            ConnectedAccountsForm,
            DeleteUserForm,
            LogoutOtherBrowserSessionsForm,
            SetPasswordForm,
            TwoFactorAuthenticationForm,
            UpdatePasswordForm,
            UpdateProfileInformationForm,
        },

        provide() {
            return {
                can: this.can,
                errors: this.errors,
                profilePageUrl: this.profilePageUrl,
                qrCode: this.qrCode,
                sessions: this.sessions,
                socialiteDrivers: this.socialiteDrivers,
                supportedLanguageOptions: this.supportedLanguageOptions,
                instructions: this.instructions,
            }
        },

        layout: (h, page) => { return h(Layout, () => page) },

        props: {
            can: { type: Object, required: true },
            errors: {type: Object, default: () => {}},
            profilePageUrl: { type: String, default: null },
            sessions: { type: Array, default:() => [] },
            socialiteDrivers: { type: Array, default:() => []},
            supportedLanguageOptions: { type: Array, default: () => [] },
            title: { type: String, default: 'Profile' },
            instructions: { type: Object, default: () => {} },
        },

        data() {
            return {
                biodataFormKey: 0,
            };
        },

        computed: {
            isConnectedAccountFormEnabled() {
                return this.socialiteDrivers.length > 0;
            },
        },

        created() {
            if (this.$page.props.flash.message !== null) {
                success('Success', this.$page.props.flash.message);
            }

            if (this.$page.props.errors.default) {
                oops({
                    text: this.$page.props.errors.default[0]
                });
            }
        },

        methods: {
            reSchema() {
                this.biodataFormKey += 1;
            },
        },
    };
</script>
