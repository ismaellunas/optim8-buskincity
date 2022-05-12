<template>
    <layout>
        <Head title="Profile" />

        <template #header>
            <h1 class="title is-2">
                Profile
            </h1>
        </template>

        <template
            v-if="description"
            #headerDescription
        >
            <p>{{ description }}</p>
        </template>


        <div class="columns is-multiline">
            <update-profile-information-form
                v-if="$page.props.jetstream.canUpdateProfileInformation"
                class="column is-12"
                :user="$page.props.user"
                :country-options="countryOptions"
                :language-options="supportedLanguageOptions"
                @after-update-profile="reSchema()"
            />

            <update-password-form
                v-if="$page.props.jetstream.canUpdatePassword && $page.props.socialstream.hasPassword"
                class="column is-12"
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
                class="column is-12"
                :user="$page.props.user"
            />

            <two-factor-authentication-form
                v-if="$page.props.jetstream.canManageTwoFactorAuthentication && $page.props.socialstream.hasPassword"
                class="column is-12"
            />

            <connected-accounts-form
                v-if="isConnectedAccountFormEnabled"
                class="column is-12"
            />

            <logout-other-browser-sessions-form
                v-if="$page.props.socialstream.hasPassword"
                :sessions="sessions"
                class="column is-12"
            />

            <div
                v-if="$page.props.jetstream.hasAccountDeletionFeatures && $page.props.socialstream.hasPassword"
                class="mb-5"
            >
                <delete-user-form class="mt-10 sm:mt-0" />
            </div>
        </div>
    </layout>
</template>

<script>
    import BiodataForm from './BiodataForm';
    import ConnectedAccountsForm from './ConnectedAccountsForm';
    import DeleteUserForm from './DeleteUserForm';
    import Layout from '@/Layouts/User';
    import LogoutOtherBrowserSessionsForm from './LogoutOtherBrowserSessionsForm';
    import SetPasswordForm from './SetPasswordForm';
    import TwoFactorAuthenticationForm from './TwoFactorAuthenticationForm';
    import UpdatePasswordForm from './UpdatePasswordForm';
    import UpdateProfileInformationForm from './UpdateProfileInformationForm';
    import { success, oops } from '@/Libs/alert';
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        name: 'ProfileShowFrontend',

        components: {
            BiodataForm,
            ConnectedAccountsForm,
            DeleteUserForm,
            Head,
            Layout,
            LogoutOtherBrowserSessionsForm,
            SetPasswordForm,
            TwoFactorAuthenticationForm,
            UpdatePasswordForm,
            UpdateProfileInformationForm,
        },

        provide() {
            return {
                can: this.can,
                countryOptions: this.countryOptions,
                errors: this.errors,
                profilePageUrl: this.profilePageUrl,
                qrCode: this.qrCode,
                sessions: this.sessions,
                socialiteDrivers: this.socialiteDrivers,
                supportedLanguageOptions: this.supportedLanguageOptions,
            }
        },

        props: {
            can: { type: Object, required: true },
            countryOptions: { type: Array, default: () => [] },
            description: { type: String, default: null },
            errors: {type: Object, default: () => {}},
            profilePageUrl: { type: String, default: null },
            sessions: { type: Array, default:() => [] },
            socialiteDrivers: { type: Array, default:() => []},
            supportedLanguageOptions: { type: Array, default: () => [] },
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
