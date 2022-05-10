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
    import StreetPerformersYouMightLike from '@/Biz/Widget/StreetPerformersYouMightLike';
    import UpcomingEvents from '@/Biz/Widget/UpcomingEvents';

    export default {
        name: 'BizWidgetColumns',

        components: {
            StreetPerformersYouMightLike,
            UpcomingEvents,
        },

        props: {
            widgets: {
                type: Array,
                default:() => [],
            },
        }
    }
</script>