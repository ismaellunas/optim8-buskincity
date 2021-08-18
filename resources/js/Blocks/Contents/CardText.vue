<template>
    <div>
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
        />

        <div class="card sdb-card-text">
            <div class="card-content">
                <div class="content" :class="cardContentClass">
                    <template v-if="isEditMode">
                        <sdb-editor v-model="entity.content.cardContent.content.html"/>
                    </template>
                    <template v-else>
                        <div v-html="entity.content.cardContent.content.html"></div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeContentMixin from '@/Mixins/EditModeContent';
    import SdbEditor from '@/Sdb/EditorTinymce';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { useModelWrapper } from '@/Libs/utils'

    export default {
        mixins: [
            EditModeContentMixin,
            DeletableContentMixin
        ],
        components: {
            SdbEditor,
            SdbToolbarContent,
        },
        props: {
            id: {},
            isEditMode: {type: Boolean, default: false},
            modelValue: {},
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
