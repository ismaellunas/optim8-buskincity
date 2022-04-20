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
                countryOptions: this.countryOptions,
                errors: this.errors,
                sessions: this.sessions,
                supportedLanguageOptions: this.supportedLanguageOptions,
            }
        },

        props: {
            countryOptions: { type: Array, default: () => [] },
            errors: {type: Object, default: () => {}},
            sessions: { type: Array, default:() => [] },
            supportedLanguageOptions: { type: Array, default: () => [] },
        },
    };
</script>
