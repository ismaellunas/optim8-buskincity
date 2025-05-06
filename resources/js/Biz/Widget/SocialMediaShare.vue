<template>
    <div class="column is-6-desktop is-6-tablet is-12-mobile">
        <h2 class="title is-4">
            {{ title }}
        </h2>
        <div class="box is-shadowless">
            <p>
                {{ data.description }}
            </p>

            <div class="buttons are-small mt-5">
                <a
                    :href="data.profilePageUrl"
                    class="button is-primary"
                    target="_blank"
                >
                    <span class="icon is-small">
                        <i class="fa-solid fa-arrow-up-right-from-square" />
                    </span>
                    <span class="has-text-weight-bold">View Page</span>
                </a>

                <biz-social-media-share :data="data.socialMediaShare" />
            </div>
        </div>
    </div>
</template>

<script>
    import BizSocialMediaShare from '@/Biz/SocialMediaShare.vue';

    export default {
        name: 'SocialMediaShare',

        components: {
            BizSocialMediaShare,
        },

        props: {
            data: {type: Object, required: true},
            title: {type: String, default: ""},
            order: {type: Number, required: true},
        },
    };
</script>
