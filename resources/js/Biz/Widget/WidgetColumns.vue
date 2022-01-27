<template>
    <div class="columns is-multiline">
        <div
            v-for="(widget, index) in widgets"
            :key="index"
            class="column is-half"
        >
            <component
                :is="widget.componentName"
                :data="widget.data"
                :title="widget.title"
            />
        </div>
    </div>
</template>

<script>
    import Post from '@/Biz/Widget/Post';
    import User from '@/Biz/Widget/User';

    export default {
        name: 'BizWidgetColumns',

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