<template>
    <div>
        <sdb-toolbar-content
            @delete-content="deleteContent"
        />

        <div class="card sdb-card-text">
            <div class="card-content">
                <div class="content" :class="cardContentClass">
                    <sdb-editor v-model="entity.content.cardContent.content.html"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeComponentMixin from '@/Mixins/EditModeComponent';
    import SdbEditor from '@/Sdb/EditorTinymce';
    import SdbToolbarContent from '@/Blocks/Backend/Contents/ToolbarContent';
    import { useModelWrapper } from '@/Libs/utils'

    export default {
        mixins: [
            EditModeComponentMixin,
            DeletableContentMixin
        ],
        components: {
            SdbEditor,
            SdbToolbarContent,
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
