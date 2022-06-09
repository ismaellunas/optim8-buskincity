<template>
    <biz-modal-card
        :content-class="['is-huge']"
        :is-close-hidden="true"
    >
        <template #header>
            <p class="modal-card-title mb-1">
                Image Editor : {{ fileName ?? ''}}
            </p>
            <biz-button
                aria-label="close"
                class="delete is-primary"
                type="button"
                :disabled="isProcessing"
                @click="$emit('close')"
            />
        </template>

        <template #footer>
            <template v-if="isCropState">
                <div class="column">
                    <biz-button
                        type="button"
                        @click="reset"
                    >
                        Reset
                    </biz-button>
                </div>
                <div class="column">
                    <div class="buttons has-addons is-centered">
                        <biz-button
                            type="button"
                            :class="{'is-primary': (aspectRatio == null)}"
                            :disabled="isProcessing"
                            @click="setAspectRatio(null)"
                        >
                            Free
                        </biz-button>
                        <biz-button
                            type="button"
                            :class="{'is-primary': (aspectRatio == 16/9)}"
                            :disabled="isProcessing"
                            @click="setAspectRatio(16/9)"
                        >
                            16:9
                        </biz-button>
                        <biz-button
                            type="button"
                            :class="{'is-primary': (aspectRatio == 4/3)}"
                            :disabled="isProcessing"
                            @click="setAspectRatio(4/3)"
                        >
                            4:3
                        </biz-button>
                        <biz-button
                            type="button"
                            :class="{'is-primary': (aspectRatio == 1)}"
                            :disabled="isProcessing"
                            @click="setAspectRatio(1)"
                        >
                            1:1
                        </biz-button>
                    </div>
                </div>
                <div class="column">
                    <div class="is-pulled-right">
                        <biz-button
                            type="button"
                            @click="disableState"
                        >
                            Cancel
                        </biz-button>
                        <biz-button
                            class="is-primary"
                            type="button"
                            @click="cropAndReplace"
                        >
                            Done
                        </biz-button>
                    </div>
                    <div class="is-clearfix"></div>
                </div>
            </template>

            <template v-else-if="isResizeState">
                <div class="column" />
                <div class="column is-6">
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
                            @click="disableState"
                        >
                            Cancel
                        </biz-button>
                        <biz-button
                            class="is-primary"
                            type="button"
                            @click="resizeAndReplace"
                        >
                            Resize
                        </biz-button>
                    </div>
                    <div class="is-clearfix" />
                </div>
            </template>

            <template v-else>
                <div class="column" />
                <div class="column has-text-centered">
                    <div class="columns">
                        <div class="column py-0">
                            <biz-button-icon
                                icon-class="is-small"
                                icon="fas fa-crop-alt"
                                title="Crop"
                                type="button"
                                :disabled="isProcessing"
                                @click="enableCropState"
                            />
                            <biz-button-icon
                                icon="fas fa-undo-alt"
                                icon-class="is-small"
                                title="Rotate Counterclockwise"
                                type="button"
                                :disabled="isProcessing"
                                @click="rotateLeft"
                            />
                            <biz-button-icon
                                icon="fas fa-redo-alt"
                                icon-class="is-small"
                                title="Rotate Clockwise"
                                type="button"
                                :disabled="isProcessing"
                                @click="rotateRight"
                            />
                            <biz-button-icon
                                icon="fas fa-arrows-alt-h"
                                icon-class="is-small"
                                title="Flip Horizontal"
                                type="button"
                                :disabled="isProcessing"
                                @click="flipX($event)"
                            />
                            <biz-button-icon
                                icon="fas fa-arrows-alt-v"
                                icon-class="is-small"
                                title="Flip Vertical"
                                type="button"
                                :disabled="isProcessing"
                                @click="flipY($event)"
                            />
                            <biz-button-icon
                                icon="fas fa-expand"
                                icon-class="is-small"
                                title="Resize"
                                type="button"
                                :disabled="isProcessing"
                                @click="enableResizeState"
                            />
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="is-pulled-right">
                        <slot
                            name="actions"
                            :cropper="cropper"
                        />
                    </div>
                    <div class="is-clearfix" />
                </div>
            </template>
        </template>

        <div id="image-editor-container">
            <vue-cropper
                ref="cropper"
                alt="Source Image"
                drag-mode="move"
                :auto-crop-area="1"
                :auto-crop="false"
                :check-cross-origin="true"
                :src="previewFileSrc"
                :min-container-height="400"
                @crop="updateImageData"
            />
        </div>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizFormFieldHorizontal from '@/Biz/Form/FieldHorizontal';
    import BizInput from '@/Biz/Input';
    import BizModalCard from '@/Biz/ModalCard';
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
        props: {
            cropper: {},
            fileName: String,
            isDebugMode: {type: Boolean, default: false},
            isProcessing: {Boolean, default: false},
            modelValue: {},
            updateImage: {},
        },
        emits: [
            'close',
            'update:cropper',
            'update:modelValue'
        ],
        setup(props, { emit }) {
            return {
                previewFileSrc: useModelWrapper(props, emit),
                cropper: useModelWrapper(props, emit, 'cropper'),
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
        },
        mounted() {
            this.cropper = this.$refs.cropper;
        },
        methods: {
            enableCropState() {
                this.state = this.stateOptions.crop;
                this.cropper
                    .initCrop()
                    .setDragMode("crop");
            },
            enableResizeState() {
                this.state = this.stateOptions.resize;
            },
            disableState() {
                this.cropper
                    .reset()
                    .clear()
                    .setDragMode('move');
                this.state = null;
            },
            cropAndReplace() {
                const self = this;
                getCanvasBlob(this.cropper.getCroppedCanvas())
                    .then(blob => {
                        const objectURL = URL.createObjectURL(blob);
                        self.previewFileSrc = objectURL;
                        self.cropper.replace(objectURL, false);
                    });

                this.disableState();
            },
            resizeAndReplace() {
                const self = this;
                let resizeData = {};

                for (const property in self.resize) {
                    if (!isEmpty(self.resize[property])) {
                        resizeData[property] = self.resize[property];
                    }
                }

                this.cropper.initCrop();
                getCanvasBlob(this.cropper.getCroppedCanvas(resizeData))
                    .then(blob => {
                        const objectURL = URL.createObjectURL(blob);
                        self.previewFileSrc = objectURL;
                        self.cropper.replace(objectURL, false);

                        self.resize.width = null;
                        self.resize.height = null;

                        self.disableState();
                    });
            },
            rotateRight() {
                this.cropper.rotate(90);
            },
            rotateLeft() {
                this.cropper.rotate(-90);
            },
            flipY(event) {
                const dom = event.currentTarget;
                let scale = dom.getAttribute('data-scale');
                scale = scale ? -scale : -1;
                this.cropper.scaleY(scale);
                dom.setAttribute('data-scale', scale);
            },
            flipX(event) {
                const dom = event.currentTarget;
                let scale = dom.getAttribute('data-scale');
                scale = scale ? -scale : -1;
                this.cropper.scaleX(scale);
                dom.setAttribute('data-scale', scale);
            },
            reset() {
                this.cropper
                    .reset()
                    .setAspectRatio(null);
                this.aspectRatio = null;
            },
            setAspectRatio(ratio) {
                this.cropper.setAspectRatio(ratio);
                this.aspectRatio = ratio;
            },
            updateImageData() {
                if (this.isDebugMode) {
                    this.imageData = this.cropper.getData(true)
                }
            },
        },
    };
</script>
