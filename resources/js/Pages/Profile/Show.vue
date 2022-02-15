<template>
    <app-layout>
        <template #header>
            Profile
        </template>

        <div class="mx-auto py-10">
            <div
                v-if="$page.props.jetstream.canUpdateProfileInformation"
                class="mb-5"
            >
                <update-profile-information-form :user="$page.props.user" />
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

            <div class="mb-5">
                <biodata-form
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
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout'
    import DeleteUserForm from './DeleteUserForm'
    import LogoutOtherBrowserSessionsForm from './LogoutOtherBrowserSessionsForm'
    import TwoFactorAuthenticationForm from './TwoFactorAuthenticationForm'
    import SetPasswordForm from './SetPasswordForm'
    import UpdatePasswordForm from './UpdatePasswordForm'
    import UpdateProfileInformationForm from './UpdateProfileInformationForm'
    import ConnectedAccountsForm from './ConnectedAccountsForm';
    import BiodataForm from './BiodataForm';
    import { success, failed } from '@/Libs/alert';

    export default {
        components: {
            BiodataForm,
            ConnectedAccountsForm,
            AppLayout,
            DeleteUserForm,
            LogoutOtherBrowserSessionsForm,
            TwoFactorAuthenticationForm,
            SetPasswordForm,
            UpdatePasswordForm,
            UpdateProfileInformationForm,
        },

        props: {
            sessions: {
                type: Array,
                default:() => [],
            },
        },

        created() {
            if (this.$page.props.success.message !== null) {
                success('Success', this.$page.props.success.message);
            }

            if (this.$page.props.failed.message !== null) {
                failed('Failed', this.$page.props.failed.message);
            }
        }
    }
</script>
