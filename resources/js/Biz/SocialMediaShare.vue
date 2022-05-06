<template>
    <div>
        <template
            v-for="(socialmedia, network) in data"
            :key="network"
        >
            <ShareNetwork
                :class="`button as-text-black ml-1 ` + socialmedia.class"
                :network="network"
                :url="socialmedia.url"
                :title="socialmedia.title"
                :description="socialmedia.description"
                :hashtags="socialmedia.hashtags"
            >
                <span class="icon">
                    <i :class="socialmedia.icon" />
                </span>
                &nbsp; {{ titleCase(network) }}
            </ShareNetwork>
        </template>
    </div>
</template>

<script>
    export default {
        name: 'SocialMediaShare',

        props: {
            data: {type: Object, default:() => {}},
        },

        methods: {
            titleCase(string) {
                return string[0].toUpperCase() + string.slice(1).toLowerCase();
            }
        },
    }
</script>