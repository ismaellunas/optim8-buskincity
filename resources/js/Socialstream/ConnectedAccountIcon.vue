<template>
    <component :is="provider + `-icon`" />
</template>

<script>
    import FacebookIcon from './ProviderIcons/FacebookIcon.vue';
    import GoogleIcon from './ProviderIcons/GoogleIcon.vue';
    import TwitterIcon from './ProviderIcons/TwitterIcon.vue';
    import LinkedinIcon from './ProviderIcons/LinkedInIcon.vue';
    import GithubIcon from './ProviderIcons/GithubIcon.vue';
    import GitlabIcon from './ProviderIcons/GitLabIcon.vue';
    import BitbucketIcon from './ProviderIcons/BitbucketIcon.vue';

    export default {
        components: {
            BitbucketIcon,
            FacebookIcon,
            GithubIcon,
            GitlabIcon,
            GoogleIcon,
            LinkedinIcon,
            TwitterIcon,
        },

        props: {
            provider: {type: String, default: null},
        },
    }
</script>
