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
    import Post from '@/Biz/Widget/Post';
    import User from '@/Biz/Widget/User';

    export default {
        name: 'BizWidgetColumnsAdmin',

        components: {
            Post,
            User,
        },

        props: {
            widgets: {
                type: Array,
                default:() => [],
            },
        }
    }
</script>
