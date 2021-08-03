<template>
    <div class="page-component" @click="$emit('setting-content', id)">
        <sdb-toolbar-content v-if="isEditMode" @delete-content="deleteContent"/>

        <template v-if="isEditMode">
            <component
                :class="headingClass"
                :is="headingTag"
            >
                <sdb-ckeditor-inline
                    v-model="entity.content.heading.html"
                    :config="editorConfig"
                />
            </component>
        </template>
        <template v-else>
            <component
                :class="headingClass"
                :is="headingTag"
                v-html="entity.content.heading.html"
            />
        </template>
    </div>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeContentMixin from '@/Mixins/EditModeContent';
    import SdbCkeditorInline from '@/Sdb/CkeditorInline';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { heading as editorConfig } from '@/Libs/ckeditor-configs';
    import { useModelWrapper } from '@/Libs/utils';
    import { last } from 'lodash';

    export default {
        mixins: [
            EditModeContentMixin,
            DeletableContentMixin
        ],
        components: {
            SdbCkeditorInline,
            SdbToolbarContent,
        },
        props: {
            id: {},
            isEditMode: {type: Boolean, default: false},
            modelValue: {type: Object},
        },
        data() {
            return {
                editorConfig: editorConfig,
            };
        },
        setup(props, { emit }) {
            return {
                entity: useModelWrapper(props, emit),
                config: props.modelValue.config,
            };
        },
        computed: {
            headingTag() {
                return this.config.heading?.tag ?? 'h1';
            },
            headingClass() {
                let classes = [];
                classes.push(this.config.heading?.type ?? 'title');

                const number = last(this.headingTag);
                classes.push('is-'+number);

                return classes;
            }
        }
    }
</script>
