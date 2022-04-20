<template>
    <app-layout>
        <template #header>
            Profile
        </template>

        <show-content />
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import ShowContent from './Show';

    export default {
        name: 'ProfileShow',

        components: {
            AppLayout,
            ShowContent,
        },

        provide() {
            return {
                can: this.can,
                countryOptions: this.countryOptions,
                errors: this.errors,
                profilePageUrl: this.profilePageUrl,
                sessions: this.sessions,
                supportedLanguageOptions: this.supportedLanguageOptions,
                qrCodeIsDisplayed: this.qrCodeIsDisplayed,
                qrCodeLogo: this.qrCodeLogo,
                qrCodeLogoName: this.qrCodeLogoName,
            }
        },

        props: {
            can: { type: Object, required: true },
            countryOptions: { type: Array, default: () => [] },
            errors: {type: Object, default: () => {}},
            profilePageUrl: { type: String, default: null },
            sessions: { type: Array, default:() => [] },
            supportedLanguageOptions: { type: Array, default: () => [] },
            qrCodeIsDisplayed: { type: Boolean, default: false },
            qrCodeLogo: { type: String, default: null },
            qrCodeLogoName: { type: String, default: null },
        },
    };
</script>
