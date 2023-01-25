<template>
    <div>
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

        <div class="columns is-multiline">
            <template
                v-for="(widget, order) in moduleWidgets"
                :key="order"
            >
                <component
                    :is="asyncComponents[ widget.componentName ]"
                    :columns="widget.columns"
                    :data="widget.data"
                    :order="order"
                    :title="widget.title"
                />
            </template>
        </div>
    </div>
</template>

<script>
    import QrCode from '@/Biz/Widget/QrCode';
    import SocialMediaShare from '@/Biz/Widget/SocialMediaShare';
    import StreetPerformersYouMightLike from '@/Biz/Widget/StreetPerformersYouMightLike';
    import StripeConnect from '@/Biz/Widget/StripeConnect';
    import UpcomingEvents from '@/Biz/Widget/UpcomingEvents';
    import WantToBecomeAStreetPerformer from '@/Biz/Widget/WantToBecomeAStreetPerformer';
    import { defineAsyncComponent } from 'vue';

    export default {
        name: 'BizWidgetColumns',

        components: {
            QrCode,
            SocialMediaShare,
            StreetPerformersYouMightLike,
            StripeConnect,
            UpcomingEvents,
            WantToBecomeAStreetPerformer,
        },

        props: {
            widgets: {
                type: Array,
                default:() => [],
            },
            moduleWidgets: { type: Array, default:() => [] },
        },

        setup(props) {

            const asyncComponents = {};

            props.moduleWidgets.forEach((widget) => {
                if (widget.moduleName == 'Booking') {
                    asyncComponents[widget.componentName] = defineAsyncComponent(() => import('@booking/Widgets/'+widget.componentName));
                }
            });

            return {
                asyncComponents,
            };
        },
    };
</script>