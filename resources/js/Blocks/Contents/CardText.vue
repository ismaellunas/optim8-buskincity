<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <div
            class="card biz-card-text"
            :class="cardClasses"
        >
            <div class="card-content">
                <div
                    class="content"
                    :class="cardContentClass"
                >
                    <biz-editor v-model="entity.content.cardContent.content.html" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizEditor from '@/Biz/EditorTinymce';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { concat } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'CardText',
        components: {
            BizEditor,
            BizToolbarContent,
        },
        mixins: [
            MixinContentHasDimension,
            MixinDeletableContent,
            MixinDuplicableContent
        ],
        props: {
            id: { type: String, required: true },
            modelValue: { type: Object, required: true },
        },
        setup(props, { emit }) {
            return {
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
            };
        },
        computed: {
            configCard() {
                return this.config.card;
            },
            cardContentClass() {
                let classes = [];
                classes.push(this.config.content?.size ?? 'is-normal');
                return classes;
            },
            cardClasses() {
                return concat(
                    this.configCard.rounded,
                    (this.configCard.isShadowless ? 'is-shadowless' : ''),
                ).filter(Boolean);
            },
        }
    }
</script>
