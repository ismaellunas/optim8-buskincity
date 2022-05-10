<template>
    <component :is="provider + `-icon`" />
</template>

<script>
    import FacebookIcon from './ProviderIcons/FacebookIcon';
    import GoogleIcon from './ProviderIcons/GoogleIcon';
    import TwitterIcon from './ProviderIcons/TwitterIcon';
    import LinkedinIcon from './ProviderIcons/LinkedInIcon';
    import GithubIcon from './ProviderIcons/GithubIcon';
    import GitlabIcon from './ProviderIcons/GitLabIcon';
    import BitbucketIcon from './ProviderIcons/BitbucketIcon';

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
