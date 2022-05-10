<template>
    <div class="columns is-multiline">
        <template
            v-for="(widget, index) in widgets"
            :key="index"
        >
            <component
                :is="widget.componentName"
                :data="widget.data"
                :title="widget.title"
                :order="index"
            />
        </template>
    </div>
</template>

<script>
    import Post from '@/Biz/Widget/Post';
    import QrCode from '@/Biz/Widget/QrCode';
    import SocialMediaShare from '@/Biz/Widget/SocialMediaShare';
    import StreetPerformersYouMightLike from '@/Biz/Widget/StreetPerformersYouMightLike';
    import User from '@/Biz/Widget/User';

    export default {
        name: 'BizWidgetColumns',

        components: {
            Post,
            QrCode,
            SocialMediaShare,
            StreetPerformersYouMightLike,
            User,
        },

        props: {
            widgets: {
                type: Array,
                default:() => [],
            },
        }
    }
</script>