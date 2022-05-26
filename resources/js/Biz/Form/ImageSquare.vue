<template>
    <div :class="wrapperClass">
        <div class="level">
            <div class="level-left mr-4">
                <div class="field is-narrow">
                    <biz-image
                        v-if="computedPhotoUrl"
                        ratio="is-128x128"
                        rounded="is-rounded"
                        :src="computedPhotoUrl"
                    />

                    <slot
                        v-else
                        name="default-image-view"
                    />
                </div>
            </div>
            <div class="level-right">
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
                        v-if="showDeleteButton"
                        class="is-danger mt-2 mr-2"
                        icon="far fa-minus-square"
                        icon-class="is-small"
                        type="button"
                        :disabled="disabled"
                        @click.prevent="$emit('on-delete-image', $event)"
                    >
                        <span class="has-text-weight-bold">{{ labelDelete }}</span>
                    </biz-button-icon>

                    <biz-button-icon
                        v-if="computedPhoto"
                        class="is-warning mt-2 mr-2"
                        icon="far fa-history"
                        icon-class="is-small"
                        type="button"
                        :disabled="disabled"
                        @click.prevent="resetPreview"
                    >
                        <span class="has-text-weight-bold">{{ labelReset }}</span>
                    </biz-button-icon>

                    <template #error>
                        <biz-input-error :message="message" />
                    </template>
                </biz-form-field>
            </div>
        </div>

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
    import BizButton from '@/Biz/Button';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizFormField from '@/Biz/Form/Field';
    import BizImage from '@/Biz/Image';
    import BizInputError from '@/Biz/InputError';
    import BizInputFile from '@/Biz/InputFile';
    import BizModalImageEditorSquare from '@/Biz/Modal/ImageEditorSquare';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { includes, last, pull } from 'lodash';
    import { oops as oopsAlert } from '@/Libs/alert';
    import { useModelWrapper, getCanvasBlob } from '@/Libs/utils';

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
            wrapperClass: {
                type: [Array, Object, String],
                default: '',
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
                    self.getCropper().getCroppedCanvas()
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

                this.$emit('on-reset-preview');
            }
        },
    };
</script>
