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
                    />
                </template>

                <template
                    v-else
                >
                    <component
                        :is="widget.componentName"
                        :columns="widget.columns"
                        :data="widget.data"
                        :order="order"
                        :title="widget.title"
                    />
                </template>
            </template>
        </div>
    </div>
</template>

<script>
    import LatestRegistration from '@/Biz/Widget/LatestRegistration.vue';
    import { defineAsyncComponent } from 'vue';
    import { sortBy } from 'lodash';

    export default {
        name: 'BizWidgetColumnsAdmin',

        components: {
            LatestRegistration,
        },

        props: {
            widgets: { type: Array, default:() => [] },
            moduleWidgets: { type: Array, default:() => [] },
        },

        setup(props) {

            const asyncComponents = {};

            props.moduleWidgets.forEach((widget) => {
                if (widget.moduleName == 'FormBuilder') {
                    asyncComponents[widget.componentName] = defineAsyncComponent(() => import('@formbuilder/Widgets/'+widget.componentName));
                }

                if (widget.moduleName == 'Booking') {
                    asyncComponents[widget.componentName] = defineAsyncComponent(() => import('@booking/Widgets/'+widget.componentName));
                }
            });

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
