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
                v-if="modelValue.note"
                #note
            >
                <p
                    class="help"
                >
                    {{ modelValue.note }}
                </p>
            </template>
        </form-file-upload>
    </div>
</template>

<script>
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import FormFileUpload from '@/Biz/Form/FileUpload.vue';

    export default {
        name: 'InputFileDragDrop',

        components: {
            BizToolbarContent,
            FormFileUpload,
        },

        mixins: [
            MixinDuplicableContent,
        ],

        props: {
            id: { type: String, required: true },
            modelValue: { type: Object, required: true },
        },

        emits: ['delete-content'],

        data() {
            return {
                value: [],
            };
        },
    }
</script>
