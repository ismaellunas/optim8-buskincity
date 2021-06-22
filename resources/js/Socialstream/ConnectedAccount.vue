<template>
    <div>
        <div class="px-3">
            <div class="is-flex is-align-items-center">

                <facebook-icon class="mr-2" v-if="provider === 'facebook'"/>
                <google-icon class="mr-2" v-if="provider === 'google'"/>
                <twitter-icon class="mr-2" v-if="provider === 'twitter'"/>
                <linked-in-icon class="mr-2" v-if="provider === 'linkedin'"/>
                <github-icon class="mr-2" v-if="provider === 'github'"/>
                <git-lab-icon class="mr-2" v-if="provider === 'gitlab'"/>
                <bitbucket-icon class="mr-2" v-if="provider === 'bitbucket'"/>

                <div>
                    <div class="">
                        {{ provider.charAt(0).toUpperCase() + provider.slice(1)  }}
                    </div>

                    <div v-if="createdAt !== null" class="">
                        Connected {{ createdAt }}
                    </div>

                    <div v-else class="">
                        Not connected.
                    </div>
                </div>
            </div>

            <slot name="action"></slot>
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
        props: {
            provider: String,
            createdAt: {
                type: String,
                default: null,
            }
        },

        components: {
            FacebookIcon,
            GoogleIcon,
            TwitterIcon,
            LinkedInIcon,
            GithubIcon,
            GitLabIcon,
            BitbucketIcon
        }
    }
</script>
