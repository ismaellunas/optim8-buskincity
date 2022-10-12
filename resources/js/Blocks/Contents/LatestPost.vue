<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <div class="columns">
            <div
                v-for="n in config.post.limit"
                :key="n"
                class="column is-4"
            >
                <article class="box is-clipped p-0">
                    <figure>
                        <img src="https://dummyimage.com/600x400/ccc/fff.png&text=+">
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
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';

    export default {
        name: 'LatestPost',

        components: {
            BizToolbarContent,
        },

        mixins: [
            MixinContentHasDimension,
            MixinDeletableContent,
            MixinDuplicableContent
        ],

        props: {
            modelValue: { type: Object, required: true },
        },

        setup(props) {
            return {
                config: props.modelValue.config,
            };
        },
    }
</script>