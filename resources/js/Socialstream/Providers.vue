<template>
    <div>
        <div class="flex flex-row items-center justify-between py-4 text-gray-500">
            <hr class="w-full mr-2">
                Or
            <hr class="w-full ml-2">
        </div>

        <div class="flex items-center justify-center">
            <a v-if="$page.props.socialstream.providers.includes('facebook')" :href="route('oauth.redirect', 'facebook')">
                <facebook-icon class="h-6 w-6 mx-2" />
                <span class="sr-only">Facebook</span>
            </a>

            <a v-if="$page.props.socialstream.providers.includes('google')" :href="route('oauth.redirect', 'google')" >
                <google-icon class="h-6 w-6 mx-2" />
                <span class="sr-only">Google</span>
            </a>

            <a v-if="$page.props.socialstream.providers.includes('twitter')" :href="route('oauth.redirect', 'twitter')">
                <twitter-icon class="h-6 w-6 mx-2" />
                <span class="sr-only">Twitter</span>
            </a>

            <a v-if="$page.props.socialstream.providers.includes('linkedin')" :href="route('oauth.redirect', 'linkedin')">
                <linked-in-icon class="h-6 w-6 mx-2" />
                <span class="sr-only">LinkedIn</span>
            </a>

            <a v-if="$page.props.socialstream.providers.includes('github')" :href="route('oauth.redirect', 'github')">
                <github-icon class="h-6 w-6 mx-2" />
                <span class="sr-only">Github</span>
            </a>

            <a v-if="$page.props.socialstream.providers.includes('gitlab')" :href="route('oauth.redirect', 'gitlab')">
                <git-lab-icon class="h-6 w-6 mx-2" />
                <span class="sr-only">GitLab</span>
            </a>

            <a v-if="$page.props.socialstream.providers.includes('bitbucket')" :href="route('oauth.redirect', 'bitbucket')">
                <bitbucket-icon class="h-6 w-6 mx-2" />
                <span class="sr-only">BitBucket</span>
            </a>
        </div>
    </div>
</template>

<script>
    import FacebookIcon from './ProviderIcons/FacebookIcon.vue';
    import GoogleIcon from './ProviderIcons/GoogleIcon.vue';
    import TwitterIcon from './ProviderIcons/TwitterIcon.vue';
    import LinkedInIcon from './ProviderIcons/LinkedInIcon.vue';
    import GithubIcon from './ProviderIcons/GithubIcon.vue';
    import GitLabIcon from './ProviderIcons/GitLabIcon.vue';
    import BitbucketIcon from './ProviderIcons/BitbucketIcon.vue';

    export default {
        components: {
            FacebookIcon,
            GoogleIcon,
            TwitterIcon,
            LinkedInIcon,
            GithubIcon,
            GitLabIcon,
            BitbucketIcon,
        },
    }
</script>
