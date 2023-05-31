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
                <p
                    class="help is-info"
                >
                    <ul>
                        <li>Accepted file extensions:...</li>
                        <li>Max file size:...</li>
                        <li
                            v-for="(note, index) in modelValue.notes"
                            :key="index"
                        >
                            {{ note }}
                        </li>
                    </ul>
                </p>
            </template>
        </form-file-upload>
    </div>
</template>

<script>
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import MixinField from '@formbuilder/Mixins/Field';
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
            MixinField,
        ],

        emits: ['delete-content'],

        data() {
            return {
                value: [],
            };
        },
    }
</script>
