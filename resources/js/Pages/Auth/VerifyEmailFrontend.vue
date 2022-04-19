<template>
    <layout>
        <template #header>
            Verify Email
        </template>

        <verify-email
            :status="status"
        />
    </layout>
</template>

<script>
    import Layout from '@/Layouts/User';
    import VerifyEmail from './VerifyEmail';

    export default {
        name: 'VerifyEmailFrontend',

        components: {
            Layout,
            VerifyEmail,
        },

        props: {
            status: { default: null, type: String }
        },
    };
</script>
