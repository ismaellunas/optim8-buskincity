<template>
    <div class="mb-2">
        <biz-form-field
            :is-required="required"
        >
            <template #label>
                {{ label }}
            </template>

            <div
                class="mt-2 mb-2"
            >
                <biz-image
                    v-if="computedPhotoUrl"
                    rounded="is-rounded"
                    style="width: 64px;"
                    :src="computedPhotoUrl"
                />

                <slot
                    v-else
                    name="default-image-view"
                />
            </div>

            <div class="control">
                <biz-input-file
                    ref="input"
                    v-bind="$attrs"
                    v-model="computedPhoto"
                    :class="{'is-danger': message}"
                    :disabled="disabled"
                    :required="required"
                    :accept="acceptedTypes"
                    :is-name-displayed="true"
                    @blur="$emit('on-blur', $event)"
                    @on-file-picked="onFilePicked"
                />

                <biz-button
                    v-if="showDeleteButton"
                    class="is-warning mt-2"
                    type="button"
                    :disabled="disabled"
                    @click.prevent="$emit('on-delete-image', $event)"
                >
                    {{ deleteLabel }}
                </biz-button>
            </div>

            <template #error>
                <biz-input-error :message="message" />
            </template>
        </biz-form-field>

        <biz-modal-card
            v-if="isModalOpen"
            :is-close-hidden="true"
            @close="closeModal()"
        >
            <template #header>
                <p class="modal-card-title">
                    {{ modalLabel }}
                </p>
                <biz-button
                    aria-label="close"
                    class="delete is-primary"
                    type="button"
                    @click="closeModal()"
                />
            </template>

            <div class="columns">
                <div class="column">
                    <div
                        class="card"
                    >
                        <div class="card-image">
                            <biz-image
                                :src="computedPhotoUrl"
                                :img-style="{maxHeight: 500+'px'}"
                            />
                        </div>
                        <footer class="card-footer">
                            <biz-button
                                class="card-footer-item is-borderless is-shadowless"
                                type="button"
                                @click="openImageEditorModal()"
                            >
                                Edit Image
                            </biz-button>
                        </footer>
                    </div>
                </div>
            </div>

            <template #footer>
                <div
                    class="columns"
                    style="width: 100%"
                >
                    <div class="column">
                        <div class="is-pulled-right">
                            <div class="buttons">
                                <biz-button
                                    class="is-danger"
                                    type="button"
                                    @click="closeModal()"
                                >
                                    Cancel
                                </biz-button>
                                <biz-button
                                    class="is-primary"
                                    type="button"
                                    @click="isModalOpen = false"
                                >
                                    Save
                                </biz-button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </biz-modal-card>

        <biz-modal-image-editor
            v-if="isImageEdit"
            v-model="photoUrlCropper"
            v-model:cropper="cropper"
            @close="closeImageEditorModal"
        >
            <template #actions>
                <biz-button
                    type="button"
                    class="is-link"
                    :disabled="disabled"
                    @click="updateImageFile"
                >
                    Done
                </biz-button>
            </template>
        </biz-modal-image-editor>
    </div>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import BizButton from '@/Biz/Button';
    import BizFormField from '@/Biz/Form/Field';
    import BizImage from '@/Biz/Image';
    import BizInputError from '@/Biz/InputError';
    import BizInputFile from '@/Biz/InputFile';
    import BizModalCard from '@/Biz/ModalCard';
    import BizModalImageEditor from '@/Biz/Modal/ImageEditor';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { includes } from 'lodash';
    import { oops as oopsAlert } from '@/Libs/alert';
    import { useModelWrapper, getCanvasBlob } from '@/Libs/utils';

    export default {
        name: 'BizFormImageEditable',

        components: {
            BizButton,
            BizFormField,
            BizImage,
            BizInputError,
            BizInputFile,
            BizModalCard,
            BizModalImageEditor,
        },

        mixins: [
            MixinHasModal,
        ],

        inheritAttrs: false,

        props: {
            label: {
                type: String,
                default: ''
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
                required: true,
            },
            disabled: {
                type: Boolean,
                default: false
            },
            required: {
                type: Boolean,
                default: false
            },
            deleteLabel: {
                type: String,
                default: 'Remove Image',
            },
            showDeleteButton: {
                type: Boolean,
                default: false,
            },
            acceptedTypes: {
                type: Array,
                default: acceptedImageTypes,
            },
            modalLabel: {
                type: String,
                default: 'Image',
            },
        },

        emits: [
            'on-blur',
            'on-delete-image',
            'on-reset-value',
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
                cropper: null,
                isImageEdit: false,
                photoUrlCropper: null,
            };
        },

        methods: {
            onFilePicked(event) {
                let fileType = "." + this.modelValue.type.split('/')[1];

                if (includes(this.acceptedTypes, fileType)) {
                    this.$emit('update:photoUrl', event.target.result);

                    this.openModal();
                } else {
                    this.$emit('update:modelValue', null);

                    oopsAlert({
                        text: "File must be a image format!"
                    });
                }
            },

            onCloseModal() {
                this.$emit('on-reset-value');
            },

            getCropperBlob() {
                return getCanvasBlob(this.cropper.getCroppedCanvas());
            },

            updateImageFile() {
                const self = this;
                self.getCropperBlob()
                    .then((blob) => {
                        self.$emit('update:modelValue', blob);
                        self.$emit('update:photoUrl', URL.createObjectURL(blob));
                        self.closeImageEditorModal();
                    });
            },

            openImageEditorModal() {
                this.isImageEdit = true;
                this.photoUrlCropper = this.computedPhotoUrl;
            },

            closeImageEditorModal() {
                this.isImageEdit = false;
            },
        },
    };
</script>
