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
    import FacebookIcon from './ProviderIcons/FacebookIcon';
    import GoogleIcon from './ProviderIcons/GoogleIcon';
    import TwitterIcon from './ProviderIcons/TwitterIcon';
    import LinkedInIcon from './ProviderIcons/LinkedInIcon';
    import GithubIcon from './ProviderIcons/GithubIcon';
    import GitLabIcon from './ProviderIcons/GitLabIcon';
    import BitbucketIcon from './ProviderIcons/BitbucketIcon';

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
