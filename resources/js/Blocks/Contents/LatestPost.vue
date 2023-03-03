<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <div class="columns">
            <div
                v-for="n in limit"
                :key="n"
                class="column"
            >
                <article class="box is-clipped p-0">
                    <figure class="image is-3by2">
                        <img :src="media?.default_latest_post">
                    </figure>
                    <div class="p-5">
                        <h2 class="title is-5 mb-2">
                            A Good News
                        </h2>
                        <div class="content is-size-7">
                            <p>News</p>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';

    export default {
        name: 'ContentLatestPost',

        components: {
            BizToolbarContent,
        },

        mixins: [
            MixinContentHasDimension,
            MixinDeletableContent,
            MixinDuplicableContent
        ],

        inject: ['media'],

        props: {
            modelValue: { type: Object, required: true },
        },

        setup(props) {
            return {
                config: props.modelValue.config,
            };
        },

        computed: {
            limit() {
                return this.config?.post?.limit ?? 3;
            },
        },
    }
</script>