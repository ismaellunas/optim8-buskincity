<template>
    <div class="columns is-multiline">
        <template
            v-for="(widget, order) in widgets"
            :key="order"
        >
            <component
                :is="widget.componentName"
                :columns="widget.columns"
                :data="widget.data"
                :order="order"
                :title="widget.title"
            />
        </template>
    </div>
</template>

<script>
    import Post from '@/Biz/Widget/Post';
    import QrCode from '@/Biz/Widget/QrCode';
    import SocialMediaShare from '@/Biz/Widget/SocialMediaShare';
    import StreetPerformersYouMightLike from '@/Biz/Widget/StreetPerformersYouMightLike';
    import StripeConnect from '@/Biz/Widget/StripeConnect';
    import UpcomingEvents from '@/Biz/Widget/UpcomingEvents';
    import User from '@/Biz/Widget/User';
    import WantToBecomeAStreetPerformer from '@/Biz/Widget/WantToBecomeAStreetPerformer';

    export default {
        name: 'BizWidgetColumns',

        components: {
            Post,
            QrCode,
            SocialMediaShare,
            StreetPerformersYouMightLike,
            StripeConnect,
            UpcomingEvents,
            User,
            WantToBecomeAStreetPerformer,
        },

        props: {
            widgets: {
                type: Array,
                default:() => [],
            },
        }
    }
</script>