<template>
    <div
        class="column"
        :class="columnClasses"
    >
        <h2 class="title is-4">
            {{ title }}
        </h2>
        <div class="box is-shadowless">
            <p>
                {{ i18n.description }}
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
                    <span class="has-text-weight-bold">
                        {{ i18n.button_view }}
                    </span>
                </a>

                <biz-social-media-share :data="data.socialMediaShare" />
            </div>
        </div>
    </div>
</template>

<script>
    import MixinWidget from '@/Mixins/Widget';
    import BizSocialMediaShare from '@/Biz/SocialMediaShare.vue';

    export default {
        name: 'SocialMediaShare',

        components: {
            BizSocialMediaShare,
        },

        mixins: [
            MixinWidget,
        ],

        props: {
            data: {type: Object, required: true},
            title: {type: String, default: ""},
        },
    };
</script>
