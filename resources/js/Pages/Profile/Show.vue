<template>
    <div class="mx-auto py-10">
        <update-profile-information-form
            v-if="$page.props.jetstream.canUpdateProfileInformation"
            class="box mb-5"
            :user="$page.props.user"
            :language-options="supportedLanguageOptions"
            :profile-page-url="can.public_page ? profilePageUrl : null"
            @after-update-profile="reSchema()"
        />

        <update-password-form
            v-if="$page.props.jetstream.canUpdatePassword && $page.props.socialstream.hasPassword"
            class="box mt-10 mb-5"
        />

        <div
            v-else
            v-show="false"
            class="mb-5"
        >
            <set-password-form class="mt-10 sm:mt-0" />
        </div>

        <biodata-form
            :key="biodataFormKey"
            class="box mt-10 mb-5"
            :user="$page.props.user"
        />

        <two-factor-authentication-form
            v-if="$page.props.jetstream.canManageTwoFactorAuthentication && $page.props.socialstream.hasPassword"
            class="box mt-10 mb-5"
        />

        <div
            v-if="isConnectedAccountFormEnabled"
            class="mb-5"
        >
            <connected-accounts-form class="mt-10 sm:mt-0" />
        </div>

        <logout-other-browser-sessions-form
            v-if="$page.props.socialstream.hasPassword"
            class="box mt-10 mb-5"
            :sessions="sessions"
        />

        <div
            v-if="$page.props.jetstream.hasAccountDeletionFeatures && $page.props.socialstream.hasPassword"
            class="mb-5"
        >
            <delete-user-form class="mt-10 sm:mt-0" />
        </div>
    </div>
</template>

<script>
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

        inject: [
            'can',
            'errors',
            'profilePageUrl',
            'sessions',
            'socialiteDrivers',
            'supportedLanguageOptions',
        ],

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
            }
        }
    };
</script>
