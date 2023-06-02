<template>
    <div>
        <biz-toolbar-content
            @delete-content="$emit('delete-content', id)"
            @duplicate-content="duplicateContent"
        />
        <form-file-upload
            v-model="value"
            :label="modelValue.label"
            :required="modelValue.validation.rules.required"
            :disabled="true"
            :placeholder="modelValue.placeholder"
        >
            <template
                #note
            >
                <biz-field-notes
                    type="info"
                    :notes="notes"
                />
            </template>
        </form-file-upload>
    </div>
</template>

<script>
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import MixinField from '@formbuilder/Mixins/Field';
    import BizFieldNotes from '@/Biz/FieldNotes.vue';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import FormFileUpload from '@/Biz/Form/FileUpload.vue';

    export default {
        name: 'InputFileDragDrop',

        components: {
            BizFieldNotes,
            BizToolbarContent,
            FormFileUpload,
        },

        mixins: [
            MixinDuplicableContent,
            MixinField,
        ],

        emits: ['delete-content'],

        data() {
            return {
                value: [],
            };
        },

        computed: {
            notes() {
                return [
                    'Accepted file extensions:...',
                    'Max file size:...'
                ].concat(this.modelValue.notes);
            },
        },
    }
</script>
