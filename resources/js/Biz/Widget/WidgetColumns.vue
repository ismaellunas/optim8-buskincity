<template>
    <div class="columns is-multiline">
        <template
            v-for="(widget, index) in widgets"
            :key="index"
        >
            <component
                :is="widget.componentName"
                :data="widget.data"
                :title="widget.title"
                :order="index"
            />
        </template>
    </div>
</template>

<script>
    import Post from '@/Biz/Widget/Post';
    import User from '@/Biz/Widget/User';
    import StreetPerformersYouMightLike from '@/Biz/Widget/StreetPerformersYouMightLike';

    export default {
        name: 'BizWidgetColumns',

        components: {
            Post,
            User,
            StreetPerformersYouMightLike,
        },

        props: {
            widgets: {
                type: Array,
                default:() => [],
            },
        }
    }
</script>