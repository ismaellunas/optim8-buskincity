<template>
    <div>
        <div class="columns is-multiline">
            <template
                v-for="(widget, order) in allWidgets"
                :key="order"
            >
                <template
                    v-if="isAsyncComponentExists(widget.componentName)"
                >
                    <component
                        :is="asyncComponents[ widget.componentName ]"
                        :columns="widget.columns"
                        :data="widget.data"
                        :order="order"
                        :title="widget.title"
                        :i18n="widget.i18n"
                    />
                </template>
            </template>
        </div>
    </div>
</template>

<script>
    import { defineAsyncComponent } from 'vue';
    import { sortBy } from 'lodash';

    export default {
        name: 'BizWidgetColumnsAdmin',

        props: {
            widgets: { type: Array, default:() => [] },
            moduleWidgets: { type: Array, default:() => [] },
        },

        setup(props) {

            const asyncComponents = {};

            props.moduleWidgets.forEach((widget) => {
                if (widget.moduleName == 'FormBuilder') {
                    asyncComponents[widget.componentName] = defineAsyncComponent(() => import(
                        `../../../../modules/FormBuilder/Resources/assets/js/Widgets/${widget.componentName}.vue`
                    ));
                }

                if (widget.moduleName == 'Booking') {
                    asyncComponents[widget.componentName] = defineAsyncComponent(() => import(
                        `../../../../modules/Booking/Resources/assets/js/Widgets/${widget.componentName}.vue`
                    ));
                }
            });

            asyncComponents['LatestRegistration'] = defineAsyncComponent(() => import(
                '@/Biz/Widget/LatestRegistration.vue'
            ));

            return {
                asyncComponents,
            };
        },

        computed: {
            allWidgets() {
                return sortBy([
                    ...this.widgets,
                    ...this.moduleWidgets,
                ], ['order']);
            },
        },

        methods: {
            isAsyncComponentExists(componentName) {
                return !!this.asyncComponents[componentName];
            },
        },
    }
</script>
