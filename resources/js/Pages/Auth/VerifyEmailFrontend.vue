<template>
    <layout>
        <template #header>
            <h1 class="title is-2">
                Verify Email
            </h1>
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
