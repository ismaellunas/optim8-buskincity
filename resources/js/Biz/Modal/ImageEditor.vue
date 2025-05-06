<template>
    <biz-modal-card
        :content-class="['is-huge']"
        :is-close-hidden="true"
    >
        <template #header>
            <p class="modal-card-title mb-1 is-hidden-touch">
                Image Editor : {{ fileName ?? '' }}
            </p>

            <p class="modal-card-title mb-1 is-hidden-desktop">
                Image Editor
            </p>

            <biz-button
                aria-label="close"
                class="delete is-primary"
                type="button"
                :class="{ 'component-configurable': isConfig }"
                :disabled="isProcessing"
                @click="$emit('close')"
            />
        </template>

        <template #footer>
            <div class="column is-12">
                <div class="columns is-multiline is-mobile">
                    <template v-if="isCropState">
                        <div class="column is-12-mobile is-4-tablet is-4-desktop">
                            <biz-button
                                type="button"
                                :class="{ 'component-configurable': isConfig }"
                                @click="reset"
                            >
                                Reset
                            </biz-button>
                        </div>
                        <div
                            v-if="! hasDimension"
                            class="column is-12-mobile is-4-tablet is-4-desktop"
                        >
                            <div class="buttons has-addons is-centered">
                                <biz-button
                                    type="button"
                                    :class="{'is-primary': (aspectRatio == null), 'component-configurable': isConfig}"
                                    :disabled="isProcessing"
                                    @click="setAspectRatio(null)"
                                >
                                    Free
                                </biz-button>
                                <biz-button
                                    type="button"
                                    :class="{'is-primary': (aspectRatio == 16/9), 'component-configurable': isConfig}"
                                    :disabled="isProcessing"
                                    @click="setAspectRatio(16/9)"
                                >
                                    16:9
                                </biz-button>
                                <biz-button
                                    type="button"
                                    :class="{'is-primary': (aspectRatio == 4/3), 'component-configurable': isConfig}"
                                    :disabled="isProcessing"
                                    @click="setAspectRatio(4/3)"
                                >
                                    4:3
                                </biz-button>
                                <biz-button
                                    type="button"
                                    :class="{'is-primary': (aspectRatio == 1), 'component-configurable': isConfig}"
                                    :disabled="isProcessing"
                                    @click="setAspectRatio(1)"
                                >
                                    1:1
                                </biz-button>
                            </div>
                        </div>
                        <div class="column is-12-mobile is-4-tablet is-4-desktop">
                            <div class="is-pulled-right">
                                <biz-button
                                    type="button"
                                    :class="{ 'component-configurable': isConfig }"
                                    @click="disableState"
                                >
                                    Cancel
                                </biz-button>
                                <biz-button
                                    class="is-primary"
                                    type="button"
                                    :class="{ 'component-configurable': isConfig }"
                                    @click="cropAndReplace"
                                >
                                    Done
                                </biz-button>
                            </div>

                            <div class="is-clearfix" />
                        </div>
                    </template>

                    <template v-else-if="isResizeState">
                        <div class="column is-8">
                            <div class="columns">
                                <div class="column">
                                    <biz-form-field-horizontal>
                                        <template #label>
                                            Width
                                        </template>
                                        <div class="control">
                                            <biz-input
                                                v-model="resize.width"
                                                :disabled="isProcessing"
                                            />
                                        </div>
                                    </biz-form-field-horizontal>
                                </div>
                                <div class="column">
                                    <biz-form-field-horizontal>
                                        <template #label>
                                            Height
                                        </template>
                                        <div class="control">
                                            <biz-input
                                                v-model="resize.height"
                                                :disabled="isProcessing"
                                            />
                                        </div>
                                    </biz-form-field-horizontal>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="is-pulled-right">
                                <biz-button
                                    type="button"
                                    :class="{ 'component-configurable': isConfig }"
                                    @click="disableState"
                                >
                                    Cancel
                                </biz-button>
                                <biz-button
                                    class="is-primary"
                                    type="button"
                                    :class="{ 'component-configurable': isConfig }"
                                    @click="resizeAndReplace"
                                >
                                    Resize
                                </biz-button>
                            </div>
                            <div class="is-clearfix" />
                        </div>
                    </template>

                    <template v-else>
                        <div class="column is-hidden-mobile" />
                        <div class="column is-12-mobile is-6-tablet has-text-centered">
                            <div class="columns">
                                <div class="column py-0">
                                    <biz-button-icon
                                        v-if="! hasDimension"
                                        icon-class="is-small"
                                        icon="fas fa-crop-alt"
                                        title="Crop"
                                        type="button"
                                        :class="{ 'component-configurable': isConfig }"
                                        :disabled="isProcessing"
                                        @click="enableCropState"
                                    />
                                    <biz-button-icon
                                        icon="fas fa-undo-alt"
                                        icon-class="is-small"
                                        title="Rotate Counterclockwise"
                                        type="button"
                                        :class="{ 'component-configurable': isConfig }"
                                        :disabled="isProcessing"
                                        @click="rotateLeft"
                                    />
                                    <biz-button-icon
                                        icon="fas fa-redo-alt"
                                        icon-class="is-small"
                                        title="Rotate Clockwise"
                                        type="button"
                                        :class="{ 'component-configurable': isConfig }"
                                        :disabled="isProcessing"
                                        @click="rotateRight"
                                    />
                                    <biz-button-icon
                                        icon="fas fa-arrows-alt-h"
                                        icon-class="is-small"
                                        title="Flip Horizontal"
                                        type="button"
                                        :class="{ 'component-configurable': isConfig }"
                                        :disabled="isProcessing"
                                        @click="flipX($event)"
                                    />
                                    <biz-button-icon
                                        icon="fas fa-arrows-alt-v"
                                        icon-class="is-small"
                                        title="Flip Vertical"
                                        type="button"
                                        :class="{ 'component-configurable': isConfig }"
                                        :disabled="isProcessing"
                                        @click="flipY($event)"
                                    />
                                    <biz-button-icon
                                        v-if="isResizeEnabled"
                                        icon="fas fa-expand"
                                        icon-class="is-small"
                                        title="Resize"
                                        type="button"
                                        :class="{ 'component-configurable': isConfig }"
                                        :disabled="isProcessing"
                                        @click="enableResizeState"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="column is-hidden-mobile" />

                        <div class="column is-12-desktop is-12-tablet is-12-mobile">
                            <div class="is-pulled-right">
                                <slot
                                    name="actions"
                                    :cropper="cropper"
                                />
                            </div>
                            <div class="is-clearfix" />
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <div id="image-editor-container">
            <vue-cropper
                ref="cropper"
                v-bind="cropperOptions"
                alt="Source Image"
                :src="previewFileSrc"
                @crop="updateImageData"
            />
        </div>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizFormFieldHorizontal from '@/Biz/Form/FieldHorizontal.vue';
    import BizInput from '@/Biz/Input.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import { getCanvasBlob, useModelWrapper } from '@/Libs/utils';
    import { isEmpty } from 'lodash';

    import VueCropper from 'vue-cropperjs';
    import 'cropperjs/dist/cropper.css';

    export default {
        components: {
            BizButton,
            BizButtonIcon,
            BizFormFieldHorizontal,
            BizInput,
            BizModalCard,
            VueCropper,
        },
        inject: {
            isConfig: {
                default: false,
            },
        },
        props: {
            croppedImageType: {
                type: String,
                default: 'image/jpeg',
                validator(value) {
                    let availableCroppedImageType = [
                        'image/jpeg',
                        'image/png',
                    ];

                    return availableCroppedImageType.includes(value);
                },
            },
            cropper: {},
            fileName: { type: [String, null], default: "" },
            isDebugMode: { type: Boolean, default: false },
            isProcessing: { type: Boolean, default: false },
            modelValue: { type: [String, null], default: "" },
            dimension: { type: Object, default: () => {} },
            isResizeEnabled: { type: Boolean, default: true, },
        },
        emits: [
            'close',
            'update:cropper',
            'update:modelValue'
        ],
        setup(props, { emit }) {
            return {
                previewFileSrc: useModelWrapper(props, emit),
                imageCropper: useModelWrapper(props, emit, 'cropper'),
            };
        },
        data() {
            return {
                aspectRatio: null,
                imageData: null,
                resize: {
                    width: null,
                    height: null,
                },
                state: null,
                stateOptions: {
                    crop: "crop",
                    resize: "resize",
                    none: null,
                },
            }
        },
        computed: {
            isCropState() {
                return this.state === this.stateOptions.crop;
            },
            isResizeState() {
                return this.state === this.stateOptions.resize;
            },
            hasDimension() {
                return (
                    ! isEmpty(this.dimension)
                    && !! this.dimension.width
                    && !! this.dimension.height
                );
            },
            ratio() {
                return (
                    parseFloat(this.dimension.width) / parseFloat(this.dimension.height)
                );
            },
            cropperOptions() {
                if (this.hasDimension) {
                    return {
                        autoCrop: true,
                        autoCropArea: this.ratio > 1 ? 1 : this.ratio,
                        checkCrossOrigin: true,
                        cropBoxMovable: false,
                        cropBoxResizable: false,
                        dragMode: "move",
                        initialAspectRatio: this.ratio,
                        minContainerHeight: 400,
                    }
                }

                return {
                    autoCrop: false,
                    autoCropArea: 1,
                    checkCrossOrigin: true,
                    dragMode: "move",
                    minContainerHeight: 400,
                };
            },
        },
        mounted() {
            this.imageCropper = this.$refs.cropper;
        },
        methods: {
            enableCropState() {
                this.state = this.stateOptions.crop;
                this.imageCropper
                    .initCrop()
                    .setDragMode("crop");
            },
            enableResizeState() {
                this.state = this.stateOptions.resize;
            },
            disableState() {
                if (this.hasDimension) {
                    this.imageCropper
                        .reset()
                        .setDragMode('crop');
                } else {
                    this.imageCropper
                        .reset()
                        .clear()
                        .setDragMode('move');
                }

                this.state = null;
            },
            cropAndReplace() {
                const self = this;
                getCanvasBlob(
                    self.imageCropper.getCroppedCanvas(),
                    self.croppedImageType
                )
                    .then(blob => {
                        const objectURL = URL.createObjectURL(blob);
                        self.previewFileSrc = objectURL;
                        self.imageCropper.replace(objectURL, false);
                    });

                self.disableState();
            },
            resizeAndReplace() {
                const self = this;
                let resizeData = {};

                for (const property in self.resize) {
                    if (!isEmpty(self.resize[property])) {
                        resizeData[property] = self.resize[property];
                    }
                }

                this.imageCropper.initCrop();
                getCanvasBlob(this.imageCropper.getCroppedCanvas(resizeData))
                    .then(blob => {
                        const objectURL = URL.createObjectURL(blob);
                        self.previewFileSrc = objectURL;
                        self.imageCropper.replace(objectURL, false);

                        self.resize.width = null;
                        self.resize.height = null;

                        self.disableState();
                    });
            },
            rotateRight() {
                this.imageCropper.rotate(90);
            },
            rotateLeft() {
                this.imageCropper.rotate(-90);
            },
            flipY(event) {
                const dom = event.currentTarget;
                let scale = dom.getAttribute('data-scale');
                scale = scale ? -scale : -1;
                this.imageCropper.scaleY(scale);
                dom.setAttribute('data-scale', scale);
            },
            flipX(event) {
                const dom = event.currentTarget;
                let scale = dom.getAttribute('data-scale');
                scale = scale ? -scale : -1;
                this.imageCropper.scaleX(scale);
                dom.setAttribute('data-scale', scale);
            },
            reset() {
                this.imageCropper
                    .reset()
                    .setAspectRatio(null);
                this.aspectRatio = null;
            },
            setAspectRatio(ratio) {
                this.imageCropper.setAspectRatio(ratio);
                this.aspectRatio = ratio;
            },
            updateImageData() {
                if (this.isDebugMode) {
                    this.imageData = this.imageCropper.getData(true)
                }
            },
        },
    };
</script>
