<template>
    <div :class="wrapperClass">
        <div
            v-if="computedPhotoUrl || $slots.defaultImageView"
            class="field is-narrow"
        >
            <biz-image
                v-if="computedPhotoUrl"
                ratio="is-128x128"
                :rounded="isRoundedPreview ? 'is-rounded' : ''"
                :src="computedPhotoUrl"
            />

            <slot
                v-else
                name="defaultImageView"
            />
        </div>

        <biz-form-field
            :is-required="required"
        >
            <template #label>
                {{ label }}
            </template>

            <biz-input-file
                ref="input"
                v-bind="$attrs"
                v-model="photoSelected"
                file-label="Choose a picture"
                :accept="acceptedTypes"
                :class="{'is-danger': message}"
                :disabled="disabled"
                :displayed-file-name="displayedFileName"
                :is-name-displayed="true"
                :required="required"
                @blur="$emit('on-blur', $event)"
                @on-file-picked="onFilePicked"
            />

            <biz-button-icon
                v-if="isDeleteButtonShown"
                class="is-danger is-small mt-3 mr-2"
                icon="fa-solid fa-trash-can"
                icon-class="is-small"
                type="button"
                :disabled="disabled"
                @click.prevent="deleteImage($event)"
            >
                <span class="has-text-weight-bold">{{ labelDelete }}</span>
            </biz-button-icon>

            <biz-button-icon
                v-if="computedPhoto"
                class="is-warning is-small mt-3 mr-2"
                icon="far fa-history"
                icon-class="is-small"
                type="button"
                :disabled="disabled"
                @click.prevent="resetPreview"
            >
                <span class="has-text-weight-bold">{{ labelReset }}</span>
            </biz-button-icon>

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

        <biz-modal-image-editor-square
            v-if="isImageEdit"
            ref="cropperModal"
            v-model="photoUrlCropper"
            :is-huge="false"
            :modal-title="modalTitle"
            @close="[usePrevFileName(), closeImageEditorModal()]"
        >
            <template #actions>
                <biz-button
                    type="button"
                    class="is-link"
                    :disabled="disabled"
                    @click="updateImageFile()"
                >
                    Done
                </biz-button>
            </template>
        </biz-modal-image-editor-square>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizFormField from '@/Biz/Form/Field.vue';
    import BizImage from '@/Biz/Image.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizInputFile from '@/Biz/InputFile.vue';
    import BizModalImageEditorSquare from '@/Biz/Modal/ImageEditorSquare.vue';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { oops as oopsAlert } from '@/Libs/alert';
    import { getCanvasBlob, useModelWrapper } from '@/Libs/utils';
    import { includes, last, pull } from 'lodash';

    export default {
        name: 'BizFormImageSquare',

        components: {
            BizButton,
            BizButtonIcon,
            BizFormField,
            BizImage,
            BizInputError,
            BizInputFile,
            BizModalImageEditorSquare,
        },

        inheritAttrs: false,

        props: {
            acceptedTypes: {
                type: Array,
                default: acceptedImageTypes,
            },
            croppedImageType: {
                type: String,
                default: 'image/jpeg',
            },
            isRoundedPreview: {
                type: Boolean,
                default: false
            },
            label: {
                type: String,
                default: ''
            },
            labelDelete: {
                type: String,
                default: 'Remove',
            },
            labelReset: {
                type: String,
                default: 'Reset',
            },
            message: {
                type: [Array, Object, String],
                default: undefined
            },
            modelValue: {
                type: [File, Blob, null],
                required: true,
            },
            photoUrl: {
                type: [String, null],
                default: null,
            },
            disabled: {
                type: Boolean,
                default: false
            },
            originalImage: {
                type: [String, null],
                default: null
            },
            required: {
                type: Boolean,
                default: null
            },
            showDeleteButton: {
                type: Boolean,
                default: false,
            },
            modalTitle: {
                type: String,
                default: 'Image',
            },
            notes: {
                type: Array,
                default: () => [],
            },
            wrapperClass: {
                type: [Array, Object, String],
                default: '',
            },
            instructions: {
                type: Array,
                default: () => []
            },
        },

        emits: [
            'on-blur',
            'on-delete-image',
            'on-cropped-image',
            'on-reset-preview',
            'update:modelValue',
            'update:photoUrl',
        ],

        setup(props, { emit }) {
            return {
                computedPhoto: useModelWrapper(props, emit),
                computedPhotoUrl: useModelWrapper(props, emit, 'photoUrl'),
            };
        },

        data() {
            return {
                fileNames: [],
                isImageEdit: false,
                photoSelected: null,
                photoUrlCropper: null,
            };
        },

        computed: {
            displayedFileName() {
                if (this.computedPhoto && this.fileNames.length > 0) {
                    return last(this.fileNames);
                }
                return null;
            },

            isDeleteButtonShown() {
                return this.originalImage && this.computedPhotoUrl && this.showDeleteButton;
            },
        },

        methods: {
            onFilePicked(event) {
                const fileType = "." + this.photoSelected.type.split('/')[1];

                if (includes(this.acceptedTypes, fileType)) {

                    this.fileNames.push(this.photoSelected.name);

                    this.photoUrlCropper = event.target.result;

                    this.openImageEditorModal();
                } else {
                    this.closeImageEditorModal();

                    oopsAlert({
                        text: "File must be a image format!"
                    });
                }

                this.photoSelected = null;
            },

            getCropper() {
                return this.$refs.cropperModal.$refs.cropper;
            },

            updateImageFile() {
                const self = this;

                getCanvasBlob(
                    self.getCropper().getCroppedCanvas({
                        maxWidth: 4096,
                        maxHeight: 4096,
                    }),
                    self.croppedImageType
                ).then((blob) => {
                    self.computedPhoto = blob;
                    self.computedPhotoUrl = URL.createObjectURL(blob);

                    self.keepLatestFileNameOnly();

                    self.closeImageEditorModal();

                    self.$emit('on-cropped-image');
                });
            },

            openImageEditorModal() {
                this.isImageEdit = true;
            },

            usePrevFileName() {
                this.fileNames.pop();
            },

            keepLatestFileNameOnly() {
                if (this.displayedFileName) {
                    this.fileNames = [ this.displayedFileName ];
                }
            },

            closeImageEditorModal() {
                this.isImageEdit = false;
            },

            resetPreview() {
                this.usePrevFileName()

                this.computedPhoto = null;

                this.computedPhotoUrl = this.originalImage;

                this.$emit('on-reset-preview');
            },

            deleteImage(event) {
                this.$emit('on-delete-image', event);
            },
        },
    };
</script>
