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
                qrCode: this.qrCode,
            }
        },

        props: {
            can: { type: Object, required: true },
            countryOptions: { type: Array, default: () => [] },
            errors: {type: Object, default: () => {}},
            profilePageUrl: { type: String, default: null },
            sessions: { type: Array, default:() => [] },
            supportedLanguageOptions: { type: Array, default: () => [] },
            qrCode: { type: Object, required: true },
        },
    };
</script>
