<template>
    <div>
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <div class="card biz-card-text">
            <div class="card-content">
                <div class="content" :class="cardContentClass">
                    <biz-editor v-model="entity.content.cardContent.content.html"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizEditor from '@/Biz/EditorTinymce';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { useModelWrapper } from '@/Libs/utils'

    export default {
        name: 'CardText',
        mixins: [
            MixinDeletableContent,
            MixinDuplicableContent
        ],
        components: {
            BizEditor,
            BizToolbarContent,
        },
        props: {
            id: String,
            modelValue: {type: Object, required: true},
        },
        setup(props, { emit }) {
            return {
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
            };
        },
        computed: {
            cardContentClass() {
                let classes = [];
                classes.push(this.config.content?.size ?? 'is-normal');
                return classes;
            },
        }
    }
</script>
