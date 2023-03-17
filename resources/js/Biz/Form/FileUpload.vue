<template>
    <biz-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <component
            :is="mediaComponent"
            :media="listMedia"
            :is-edit-enabled="false"
            :is-scrolled="true"
            @on-delete-clicked="onDeleteMedium"
        />

        <div class="control">
            <biz-file-upload
                v-if="maxFileNumber > 0"
                :key="filePondKey"
                :accepted-types="acceptedTypes"
                :allow-multiple="allowMultiple"
                :disabled="disabled"
                :max-file-size="maxFileSizeUpload"
                :max-files="maxFileNumber"
                :max-total-file-size="maxTotalFileSizeUpload"
                :placeholder="placeholder"
                :required="isRequired"
                @on-update-files="onUpdateFiles"
            />
        </div>


        <slot name="note">
            <p
                v-if="notes"
                class="help is-info"
            >
                <ul>
                    <li
                        v-for="note, index in notes"
                        :key="index"
                    >
                        {{ note }}
                    </li>
                </ul>
            </p>
        </slot>

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizFileUpload from '@/Biz/FileUpload.vue';
    import BizFormField from '@/Biz/Form/Field.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizMediaGallery from '@/Biz/Media/Gallery.vue';
    import BizMediaText from '@/Biz/Media/Text.vue';
    import { confirmDelete } from '@/Libs/alert';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FileUpload',

        components: {
            BizFileUpload,
            BizFormField,
            BizInputError,
            BizMediaGallery,
            BizMediaText,
        },

        inheritAttrs: false,

        props: {
            acceptedTypes: {
                type: Array,
                default:() => [],
            },
            notes: {
                type: Array,
                default: () => [],
            },
            label: {
                type: String,
                default: null,
            },
            message: {
                type: Object,
                default: () => {},
            },
            modelValue: {
                type: [Object, null],
                required: true,
            },
            media: {
                type: Array,
                default:() => []
            },
            mediaComponent: {
                type: String,
                default: 'BizMediaGallery',
            },
            disabled: {
                type: Boolean,
                default: false
            },
            required: {
                type: Boolean,
                default: false
            },
            placeholder: {
                type: String,
                default: 'Drop files here...'
            },
            allowMultiple: {
                type: Boolean,
                default: false,
            },
            maxFiles: {
                type: [Number, String],
                default: 1
            },
            maxFileSize: {
                type: [Number, String, null],
                default: null
            },
            maxTotalFileSize: {
                type: [Number, String, null],
                default: null
            },
        },

        emits: [
            'on-file-picked',
        ],

        setup(props, { emit }) {
            return {
                fileUploadField: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                filePondKey: 0,
            };
        },

        computed: {
            hasMedia() {
                return this.media && this.media.length > 0;
            },

            maxFileNumber() {
                let mediaLength = 0;
                let deleteMedia = 0;

                if (this.hasMedia) {
                    mediaLength = this.media.length;
                }

                if (this.fileUploadField.deleted_media) {
                    deleteMedia = this.fileUploadField.deleted_media.length;
                }

                return this.maxFiles - mediaLength + deleteMedia;
            },

            maxFileSizeUpload() {
                return this.maxFileSize ? this.maxFileSize + `KB` : null;
            },

            maxTotalFileSizeUpload() {
                return this.maxTotalFileSize ? this.maxTotalFileSize + `KB` : null;
            },

            listMedia() {
                const self = this;

                return this.media.filter((medium) => {
                    return !self
                        .fileUploadField
                        .deleted_media
                        .includes(medium.id);
                });
            },

            isRequired() {
                return !this.hasMedia ? this.required : false;
            },
        },

        methods: {
            onUpdateFiles(files) {
                this.fileUploadField.files = files;
            },

            onDeleteMedium(medium) {
                const self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.fileUploadField.deleted_media.push(medium.id);
                    }
                });
            },

            reset() {
                this.filePondKey += 1;
            },
        },
    }
</script>