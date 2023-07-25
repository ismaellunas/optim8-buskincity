<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="columns is-multiline">
                    <template
                        v-for="(storedWidget, order) in widgetLists"
                        :key="order"
                    >
                        <component
                            :is="asyncComponents[ storedWidget.componentName ]"
                            class="column"
                            v-bind="{...storedWidget, ...{order: order}}"
                        />
                    </template>
                </div>

                <component
                    :is="asyncComponents['BizWidgetColumnsAdmin']"
                    :widgets="widgets"
                    :module-widgets="moduleWidgets"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { defineAsyncComponent } from 'vue';

    export default {
        name: 'DashboardAdmin',

        layout: AppLayout,

        props: {
            moduleWidgets: { type: Array, default:() => [] },
            title: { type: String, required: true },
            widgets: { type: Array, default:() => [] },
            storedWidgets: { type: Array, default:() => [] },
        },

        setup(props) {
            const asyncComponents = {};
            let widgets = [];

            props.storedWidgets.forEach(storedWidget => {
                const componentName = storedWidget.vueComponent;
                const module = storedWidget.vueComponentModule;

                if (! module) {
                    asyncComponents[componentName] = defineAsyncComponent(() => import(
                        `./../Biz/Widget/${componentName}.vue`
                    ));
                } else if (module == 'Booking') {
                    asyncComponents[componentName] = defineAsyncComponent(() => import(
                        `./../../../modules/Booking/Resources/assets/js/Widgets/${componentName}.vue`
                    ));
                }

                widgets.push({
                    ...storedWidget,
                    componentName,
                });
            });

            asyncComponents['BizWidgetColumnsAdmin'] = defineAsyncComponent(() => import(
                '@/Biz/Widget/WidgetColumnsAdmin.vue'
            ));

            return {
                asyncComponents,
                widgetLists: widgets,
            };
        },
    };
</script>
