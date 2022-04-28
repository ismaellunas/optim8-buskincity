<template>
    <layout>
        <template #header>
            Profile
        </template>

        <show-content />
    </layout>
</template>

<script>
    import Layout from '@/Layouts/User';
    import ShowContent from './Show';

    export default {
        name: 'ProfileShowFrontend',

        components: {
            Layout,
            ShowContent,
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
    };
</script>
