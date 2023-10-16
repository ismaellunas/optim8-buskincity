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
                            title="Rotate Counterclockwise"
                            type="button"
                            :disabled="isProcessing"
                            :icon="icon.rotateLeft"
                            @click="rotateLeft"
                        />
                        <biz-button-icon
                            title="Rotate Clockwise"
                            type="button"
                            :disabled="isProcessing"
                            :icon="icon.rotateRight"
                            @click="rotateRight"
                        />
                        <biz-button-icon
                            title="Flip Horizontal"
                            type="button"
                            :disabled="isProcessing"
                            :icon="icon.flipHorizontal"
                            @click="flipX($event)"
                        />
                        <biz-button-icon
                            title="Flip Vertical"
                            type="button"
                            :disabled="isProcessing"
                            :icon="icon.flipVertical"
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
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { computed } from 'vue';
    import { defaultCropperOptions } from '@/Libs/defaults';
    import { flipHorizontal, flipVertical, rotateLeft, rotateRight } from '@/Libs/icon-class';

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
            modelValue: { type: [String, null], default: "" },
        },

        emits: [
            'close',
            'update:modelValue'
        ],

        setup(props, { emit }) {
            return {
                mergedCropperOptions: {
                    ...defaultCropperOptions,
                    ...{
                        autoCrop: true,
                        autoCropArea: 1,
                        cropBoxMovable: false,
                        cropBoxResizable: false,
                        initialAspectRatio: 1,
                        zoomable: true,
                    },
                    ...computed(() => props.cropperOptions).value
                },
                previewFileSrc: useModelWrapper(props, emit),
                icon: {
                    flipHorizontal,
                    flipVertical,
                    rotateLeft,
                    rotateRight,
                },
            };
        },

        data() {
            return {
                imageCropper: null,
                imageData: null,
            }
        },

        mounted() {
            this.imageCropper = this.$refs.cropper;
        },

        methods: {
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

            updateImageData() {
                if (this.isDebugMode) {
                    this.imageData = this.imageCropper.getData(true)
                }
            },
        },
    };
</script>
