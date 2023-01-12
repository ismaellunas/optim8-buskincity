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
    import Post from '@/Biz/Widget/Post';
    import User from '@/Biz/Widget/User';
    import { defineAsyncComponent } from 'vue';

    export default {
        name: 'BizWidgetColumnsAdmin',

        components: {
            Post,
            User,
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
    }
</script>
