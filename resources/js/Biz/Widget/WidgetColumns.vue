<template>
    <div class="columns is-multiline is-mobile">
        <template
            v-for="(widget, order) in widgets"
            :key="order"
        >
            <component
                :is="asyncComponents[ widget.component ?? widget.componentModule ]"
                class="mb-5"
                :columns="widget.grid"
                :data="widget.data"
                :title="widget.title"
            />
        </template>
    </div>
</template>

<script>
    import { defineAsyncComponent } from 'vue';

    export default {
        name: 'BizWidgetColumns',

        props: {
            widgets: { type: Array, default:() => [] },
        },

        setup(props) {

            const asyncComponents = {};

            props.widgets.forEach((widget) => {
                if (widget.module == 'Booking' && widget.componentModule) {
                    asyncComponents[widget.componentModule] = defineAsyncComponent(() => import(
                        `../../../../modules/Booking/Resources/assets/js/Widgets/${widget.componentModule}.vue`)
                    );
                }

                if (! widget.componentModule) {
                    asyncComponents[widget.component] = defineAsyncComponent(() => import(
                        `../Widget/${widget.component}.vue`)
                    );
                }
            });

            return {
                asyncComponents,
            };
        },
    };
</script>