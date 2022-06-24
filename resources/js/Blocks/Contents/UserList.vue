<template>
    <div>
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <div class="columns">
            <div
                v-for="index in items"
                :key="index"
                class="column"
            >
                <div class="card">
                    <div class="card-content">
                        <div class="media">
                            <figure class="image is-128x128">
                                <img
                                    src="https://bulma.io/images/placeholders/128x128.png"
                                    class="is-rounded"
                                    alt="Placeholder image"
                                >
                            </figure>
                        </div>

                        <div class="content">
                            <p class="title is-4">
                                John Doe
                            </p>
                            <p class="subtitle is-6">
                                @johndoe
                            </p>
                            ...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { concat, last } from 'lodash';
    import { createMarginClasses, createPaddingClasses } from '@/Libs/page-builder';
    import { isBlank, useModelWrapper } from '@/Libs/utils';
    import { inject, ref } from "vue";

    export default {
        name: 'UserList',

        components: {
            BizToolbarContent,
        },

        mixins: [
            MixinDeletableContent,
            MixinDuplicableContent,
        ],

        props: {
            id: { type: String, default: null },
            modelValue: { type: Object, required: true },
        },

        setup(props, { emit }) {
            return {
                config: props.modelValue?.config,
                entity: useModelWrapper(props, emit),
                items: ref(4),
            };
        },

        methods: {
            onEditHeading(evt) {
                this.entity.content.heading.html = evt.target.innerText;
            },
        },
    };
</script>
