<template>
    <div class="column is-half">
        <h2 class="title is-4 mt-5">
            {{ title }}
        </h2>
        <div class="box is-shadowless">
            <p>As a performer, you have a public page to share with your audience. It's just like your unique site within BuskinCity. You can copy the link or share on your social media:</p>

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
    import BizSocialMediaShare from '@/Biz/SocialMediaShare';

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
