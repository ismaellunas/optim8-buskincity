<template>
    <app-layout>
        <template #header>
            Verify Email
        </template>

        <verify-email
            :status="status"
        />
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import VerifyEmail from './VerifyEmail.vue';

    export default {
        name: 'VerifyEmailAdmin',

        components: {
            AppLayout,
            VerifyEmail,
        },

        props: {
            status: { default: null, type: String }
        },
    };
</script>
