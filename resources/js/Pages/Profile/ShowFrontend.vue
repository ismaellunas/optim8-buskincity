<template>
    <layout>
        <template #header>
            Profile
        </template>

        <template #subheader>
            <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.</p>
        </template>

        <div class="columns is-multiline">
            <div
                v-if="can.public_page && qrCode.isDisplayed"
                class="column is-6"
            >
                <h2 class="title is-4">
                    Your QR code
                </h2>
                <div class="box is-shadowless">
                    <div class="columns">
                        <div class="column is-narrow">
                            <biz-qr-code
                                :height="128"
                                :width="128"
                                :text="profilePageUrl"
                                :logo-url="qrCode.logoUrl"
                                :name="qrCode.name"
                                @data-url-download="setDownloadUrl"
                                @data-url-print="setPrintUrl"
                            />
                        </div>

                        <div class="column">
                            <p>Print your QR code and place it on your pitch. It will allow your audience to find you on BuskinCity, send donations, book you for private gigs, or follow your work.</p>

                            <div class="buttons are-small mt-5">
                                <a
                                    :href="qrCodeUrl.download"
                                    class="button is-primary"
                                    :download="qrCode.name"
                                >
                                    <span class="has-text-weight-bold">Download</span>
                                </a>
                                <a
                                    :href="qrCodeUrl.print"
                                    class="button"
                                    :download="qrCode.name"
                                >
                                    <span class="has-text-weight-bold">Print</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="can.public_page"
                class="column is-6"
            >
                <h2 class="title is-4">
                    Share your page
                </h2>
                <div class="box is-shadowless">
                    <p>As a performer, you have a public page to share with your audience. It's just like your unique site within BuskinCity. You can copy the link or share on your social media:</p>

                    <div class="buttons are-small mt-5">
                        <a href="#" class="button is-primary" target="_blank">
                            <span class="icon is-small">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </span>
                            <span class="has-text-weight-bold">View Page</span>
                        </a>
                        <a href="#" class="button">
                            <span class="icon is-small">
                                <i class="fa-brands fa-facebook"></i>
                            </span>
                            <span class="has-text-weight-bold">Facebook</span>
                        </a>
                        <a href="#" class="button">
                            <span class="icon is-small">
                                <i class="fa-brands fa-twitter"></i>
                            </span>
                            <span class="has-text-weight-bold">Twitter</span>
                        </a>
                        <a href="#" class="button">
                            <span class="icon is-small">
                                <i class="fa-brands fa-linkedin-in"></i>
                            </span>
                            <span class="has-text-weight-bold">LinkedIn</span>
                        </a>
                    </div>
                </div>
            </div>

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
                v-if="isConnectedAccountFormEnabled"
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
    </layout>
</template>

<script>
    import BiodataForm from './BiodataForm';
    import BizQrCode from '@/Biz/QrCode';
    import ConnectedAccountsForm from './ConnectedAccountsForm';
    import DeleteUserForm from './DeleteUserForm';
    import Layout from '@/Layouts/User';
    import LogoutOtherBrowserSessionsForm from './LogoutOtherBrowserSessionsForm';
    import SetPasswordForm from './SetPasswordForm';
    import TwoFactorAuthenticationForm from './TwoFactorAuthenticationForm';
    import UpdatePasswordForm from './UpdatePasswordForm';
    import UpdateProfileInformationForm from './UpdateProfileInformationForm';
    import { success, oops } from '@/Libs/alert';

    export default {
        name: 'ProfileShowFrontend',

        components: {
            BiodataForm,
            BizQrCode,
            ConnectedAccountsForm,
            DeleteUserForm,
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
            errors: {type: Object, default: () => {}},
            profilePageUrl: { type: String, default: null },
            qrCode: { type: Object, required: true },
            sessions: { type: Array, default:() => [] },
            socialiteDrivers: { type: Array, default:() => []},
            supportedLanguageOptions: { type: Array, default: () => [] },
        },

        data() {
            return {
                biodataFormKey: 0,
                qrCodeUrl: {
                    download: null,
                    print: null,
                },
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

            setDownloadUrl(url) {
                this.qrCodeUrl.download = url;
            },

            setPrintUrl(url) {
                this.qrCodeUrl.print = url;
            },
        },
    };
</script>
