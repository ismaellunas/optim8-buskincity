<template>
    <div>
        <show-content />
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import ShowContent from './Show.vue';

    export default {
        name: 'ProfileShow',

        components: {
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
                instructions: this.instructions,
            }
        },

        layout: (h, page) => { return h(AppLayout, () => page) },

        props: {
            can: { type: Object, required: true },
            errors: {type: Object, default: () => {}},
            profilePageUrl: { type: String, default: null },
            sessions: { type: Array, default:() => [] },
            socialiteDrivers: { type: Array, default:() => []},
            supportedLanguageOptions: { type: Array, default: () => [] },
            title: { type: String, default: 'Profile' },
            instructions: { type: Object, default: () => {} },
        },
    };
</script>
