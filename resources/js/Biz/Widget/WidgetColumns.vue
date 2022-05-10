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
    import QrCode from '@/Biz/Widget/QrCode';
    import StreetPerformersYouMightLike from '@/Biz/Widget/StreetPerformersYouMightLike';
    import UpcomingEvents from '@/Biz/Widget/UpcomingEvents';
    import WantToBecomeAStreetPerformer from '@/Biz/Widget/WantToBecomeAStreetPerformer';

    export default {
        name: 'BizWidgetColumns',

        components: {
            QrCode,
            StreetPerformersYouMightLike,
            UpcomingEvents,
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