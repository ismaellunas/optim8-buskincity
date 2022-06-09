<template>
    <biz-modal-card
        :content-class="{ 'is-huge': isHuge }"
        :is-close-hidden="true"
    >
        <template #header>
            <p class="modal-card-title mb-1">
                Image Editor
                <template v-if="fileName">
                    : {{ fileName }}
                </template>
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
            <div class="column is-2" />
            <div class="column is-8 has-text-centered">
                <div class="columns">
                    <div class="column py-0">
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
                    </div>
                </div>
            </div>

            <div class="column is-2">
                <div class="is-pulled-right">
                    <slot name="actions" />
                </div>

                <div class="is-clearfix" />
            </div>
        </template>

        <div id="image-editor-container">
            <vue-cropper
                ref="cropper"
                alt="Source Image"
                v-bind="mergedCropperOptions"
                :src="previewFileSrc"
                @crop="updateImageData"
            />
        </div>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizModalCard from '@/Biz/ModalCard';
    import { getCanvasBlob, useModelWrapper } from '@/Libs/utils';
    import { merge } from 'lodash';

    import VueCropper from 'vue-cropperjs';
    import 'cropperjs/dist/cropper.css';

    export default {
        name: 'BizModalImageEditorSquare',

        components: {
            BizButton,
            BizButtonIcon,
            BizModalCard,
            VueCropper,
        },

        props: {
            cropperOptions: { type: Object, default: () => {} },
            fileName: { type: String, default: '' },
            isDebugMode: { type: Boolean, default: false },
            isHuge: { type: Boolean, default: true },
            isProcessing: { type: Boolean, default: false },
            modelValue: {},
        },

        emits: [
            'close',
            'update:modelValue'
        ],

        setup(props, { emit }) {
            const defaultCropperOptions = {
                autoCrop: true,
                autoCropArea: 1,
                checkCrossOrigin: true,
                cropBoxMovable: false,
                cropBoxResizable: false,
                dragMode: "move",
                initialAspectRatio: 1,
                minContainerHeight: 400,
            };

            return {
                mergedCropperOptions: merge(
                    defaultCropperOptions,
                    props.cropperOptions
                ),
                previewFileSrc: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                imageData: null,
            }
        },

        methods: {
            rotateRight() {
                this.$refs.cropper.rotate(90);
            },
            rotateLeft() {
                this.$refs.cropper.rotate(-90);
            },
            flipY(event) {
                const dom = event.currentTarget;
                let scale = dom.getAttribute('data-scale');
                scale = scale ? -scale : -1;
                this.$refs.cropper.scaleY(scale);
                dom.setAttribute('data-scale', scale);
            },
            flipX(event) {
                const dom = event.currentTarget;
                let scale = dom.getAttribute('data-scale');
                scale = scale ? -scale : -1;
                this.$refs.cropper.scaleX(scale);
                dom.setAttribute('data-scale', scale);
            },
            updateImageData() {
                if (this.isDebugMode) {
                    this.imageData = this.$refs.cropper.getData(true)
                }
            },
        },
    };
</script>
