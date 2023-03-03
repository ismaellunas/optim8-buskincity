<template>
    <div>
        <div class="px-3">
            <div class="is-flex is-align-items-center">
                <facebook-icon
                    v-if="provider === 'facebook'"
                    class="mr-2"
                />
                <google-icon
                    v-if="provider === 'google'"
                    class="mr-2"
                />
                <twitter-icon
                    v-if="provider === 'twitter'"
                    class="mr-2"
                />
                <linked-in-icon
                    v-if="provider === 'linkedin'"
                    class="mr-2"
                />
                <github-icon
                    v-if="provider === 'github'"
                    class="mr-2"
                />
                <git-lab-icon
                    v-if="provider === 'gitlab'"
                    class="mr-2"
                />
                <bitbucket-icon
                    v-if="provider === 'bitbucket'"
                    class="mr-2"
                />

                <div>
                    <span>
                        {{ provider.charAt(0).toUpperCase() + provider.slice(1) }}
                    </span>
                    <br>

                    <span v-if="createdAt !== null">
                        Connected {{ createdAt }}
                    </span>

                    <span v-else>
                        Not connected.
                    </span>
                </div>
            </div>

            <slot name="action" />
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
            BitbucketIcon
        },

        props: {
            provider: String,
            createdAt: {
                type: String,
                default: null,
            }
        },
    }
</script>
