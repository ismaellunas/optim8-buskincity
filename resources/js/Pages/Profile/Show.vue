<template>
    <div class="mx-auto py-10">
        <div
            v-if="can.public_page && qrCode.isDisplayed"
            class="mb-5"
        >
            <biz-action-section>
                <template #title>
                    Scan Me
                </template>

                <template #description>
                    Scan QR Code to see the performer public page.
                </template>

                <template #content>
                    <biz-qr-code
                        :is-downloadable="true"
                        :text="profilePageUrl"
                        :logo-url="qrCode.logoUrl"
                        :name="qrCode.name"
                    />
                </template>
            </biz-action-section>
        </div>

        <div
            v-if="$page.props.jetstream.canUpdateProfileInformation"
            class="mb-5"
        >
            <update-profile-information-form
                :user="$page.props.user"
                :country-options="countryOptions"
                :language-options="supportedLanguageOptions"
                :profile-page-url="can.public_page ? profilePageUrl : null"
                @after-update-profile="reSchema()"
            />
        </div>

        <div
            v-if="$page.props.jetstream.canUpdatePassword && $page.props.socialstream.hasPassword"
            class="mb-5"
        >
            <update-password-form class="mt-10 sm:mt-0" />
        </div>

        <div
            v-else
            class="mb-5"
        >
            <set-password-form class="mt-10 sm:mt-0" />
        </div>

        <div
            v-if="can.biodata_form"
            class="mb-5"
        >
            <biodata-form
                :key="biodataFormKey"
                :user="$page.props.user"
                class="mt-10 sm:mt-0"
            />
        </div>

        <div
            v-if="$page.props.jetstream.canManageTwoFactorAuthentication && $page.props.socialstream.hasPassword"
            class="mb-5"
        >
            <two-factor-authentication-form class="mt-10 sm:mt-0" />
        </div>

        <div
            v-if="$page.props.socialstream.show"
            class="mb-5"
        >
            <connected-accounts-form class="mt-10 sm:mt-0" />
        </div>

        <div
            v-if="$page.props.socialstream.hasPassword"
            class="mb-5"
        >
            <logout-other-browser-sessions-form
                :sessions="sessions"
                class="mt-10 sm:mt-0"
            />
        </div>

        <div
            v-if="$page.props.jetstream.hasAccountDeletionFeatures && $page.props.socialstream.hasPassword"
            class="mb-5"
        >
            <delete-user-form class="mt-10 sm:mt-0" />
        </div>
    </div>
</template>

<script>
    import BiodataForm from './BiodataForm';
    import BizActionSection from '@/Biz/ActionSection';
    import BizQrCode from '@/Biz/QrCode';
    import ConnectedAccountsForm from './ConnectedAccountsForm';
    import DeleteUserForm from './DeleteUserForm';
    import LogoutOtherBrowserSessionsForm from './LogoutOtherBrowserSessionsForm';
    import SetPasswordForm from './SetPasswordForm';
    import TwoFactorAuthenticationForm from './TwoFactorAuthenticationForm';
    import UpdatePasswordForm from './UpdatePasswordForm';
    import UpdateProfileInformationForm from './UpdateProfileInformationForm';
    import { success, oops } from '@/Libs/alert';

    export default {
        components: {
            BiodataForm,
            BizActionSection,
            BizQrCode,
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
            'countryOptions',
            'errors',
            'profilePageUrl',
            'sessions',
            'supportedLanguageOptions',
            'qrCode',
        ],

        data() {
            return {
                biodataFormKey: 0,
            };
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
