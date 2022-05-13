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
                errors: this.errors,
                profilePageUrl: this.profilePageUrl,
                sessions: this.sessions,
                socialiteDrivers: this.socialiteDrivers,
                supportedLanguageOptions: this.supportedLanguageOptions,
            }
        },

        props: {
            can: { type: Object, required: true },
            errors: {type: Object, default: () => {}},
            profilePageUrl: { type: String, default: null },
            sessions: { type: Array, default:() => [] },
            socialiteDrivers: { type: Array, default:() => []},
            supportedLanguageOptions: { type: Array, default: () => [] },
        },
    };
</script>
