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

                <biz-widget-columns-admin
                    :widgets="widgets"
                    :module-widgets="moduleWidgets"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizWidgetColumnsAdmin from '@/Biz/Widget/WidgetColumnsAdmin.vue';
    import { defineAsyncComponent } from 'vue';

    export default {
        name: 'DashboardAdmin',

        components: {
            BizWidgetColumnsAdmin,
        },

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
                const componentName = /[^/]*$/.exec(storedWidget.vue)[0];

                if (! storedWidget.module) {
                    asyncComponents[componentName] = defineAsyncComponent(() => import(
                        /* @vite-ignore */
                        './../'+storedWidget.vue+'.vue'
                    ));
                } else {
                    asyncComponents[componentName] = defineAsyncComponent(() => import(
                        /* @vite-ignore */
                        './../../../modules/'+storedWidget.module+'/Resources/assets/js/Widgets/'+storedWidget.vue+'.vue'
                    ));
                }

                widgets.push({
                    ...storedWidget,
                    componentName,
                });
            });

            return {
                asyncComponents,
                widgetLists: widgets,
            };
        },
    };
</script>
