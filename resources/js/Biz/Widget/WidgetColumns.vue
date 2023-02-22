<template>
    <div>
        <div class="columns is-multiline">
            <template
                v-for="(widget, order) in widgets"
                :key="order"
            >
                <component
                    :is="widget.componentName"
                    class="mb-5"
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
                    class="mb-5"
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
    import QrCode from '@/Biz/Widget/QrCode.vue';
    import SocialMediaShare from '@/Biz/Widget/SocialMediaShare.vue';
    import StreetPerformersYouMightLike from '@/Biz/Widget/StreetPerformersYouMightLike.vue';
    import StripeConnect from '@/Biz/Widget/StripeConnect.vue';
    import UpcomingEvents from '@/Biz/Widget/UpcomingEvents.vue';
    import WantToBecomeAStreetPerformer from '@/Biz/Widget/WantToBecomeAStreetPerformer.vue';
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
            widgets: { type: Array, default:() => [] },
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