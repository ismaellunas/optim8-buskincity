<template>
    <biz-modal-card
        :is-close-hidden="true"
    >
        <template #header>
            <p class="modal-card-title is-hidden-touch is-size-5">
                {{ fileName ?? 'Image Editor' }}
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
            <div class="column p-0 m-0">
                <div class="columns is-multiline is-mobile">
                    <template v-if="isCropState">
                        <div class="column is-12-mobile is-4-tablet is-3">
                            <div class="buttons">
                                <biz-button
                                    type="button"
                                    title="Cancel"
                                    :class="{ 'component-configurable': isConfig }"
                                    @click="disableState"
                                >
                                    Reset
                                </biz-button>
                            </div>
                        </div>

                        <div
                            v-if="! hasDimension"
                            class="column is-12-mobile is-4-tablet is-6"
                        >
                            <div class="buttons has-addons is-centered">
                                <biz-button
                                    v-for="ratioOption in aspectRatioOptions"
                                    :key="ratioOption.id"
                                    type="button"
                                    :class="{'is-primary': (aspectRatio == ratioOption.aspectRatio), 'component-configurable': isConfig}"
                                    :disabled="isProcessing"
                                    @click.prevent="setAspectRatio(ratioOption.aspectRatio)"
                                >
                                    {{ ratioOption.id }}
                                </biz-button>
                            </div>
                        </div>

                        <div class="column is-12-mobile is-4-tablet is-3">
                            <div class="is-pulled-right">
                                <biz-button
                                    class="is-primary"
                                    type="button"
                                    title="Apply"
                                    :class="{ 'component-configurable': isConfig }"
                                    @click="cropAndRecreate"
                                >
                                    Apply
                                </biz-button>
                            </div>

                            <div class="is-clearfix" />
                        </div>
                    </template>

                    <template v-else>
                        <div class="column is-3-mobile is-3-tablet has-text-left">
                            <slot name="leftActions" />
                        </div>

                        <div class="column is-6-mobile is-6-tablet has-text-centered">
                            <div class="buttons is-centered">
                                <biz-button-icon
                                    v-if="! hasDimension"
                                    title="Crop"
                                    type="button"
                                    :class="{ 'component-configurable': isConfig }"
                                    :disabled="isProcessing"
                                    :icon="icon.crop"
                                    @click="enableCropState"
                                />
                                <biz-button-icon
                                    title="Rotate Counterclockwise"
                                    type="button"
                                    :class="{ 'component-configurable': isConfig }"
                                    :disabled="isProcessing"
                                    :icon="icon.rotateLeft"
                                    @click="rotateLeft"
                                />
                                <biz-button-icon
                                    title="Rotate Clockwise"
                                    type="button"
                                    :class="{ 'component-configurable': isConfig }"
                                    :disabled="isProcessing"
                                    :icon="icon.rotateRight"
                                    @click="rotateRight"
                                />
                                <biz-button-icon
                                    title="Flip Horizontal"
                                    type="button"
                                    :class="{ 'component-configurable': isConfig }"
                                    :disabled="isProcessing"
                                    :icon="icon.flipHorizontal"
                                    @click="flipX($event)"
                                />
                                <biz-button-icon
                                    title="Flip Vertical"
                                    type="button"
                                    :class="{ 'component-configurable': isConfig }"
                                    :disabled="isProcessing"
                                    :icon="icon.flipVertical"
                                    @click="flipY($event)"
                                />
                            </div>
                        </div>

                        <div class="column is-3-mobile is-3-tablet">
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
                @ready="cropperReady"
            />
        </div>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { isEmpty } from 'lodash';
    import { checkCircle, crop, flipHorizontal, flipVertical, rotateLeft, rotateRight } from '@/Libs/icon-class';
    import { ref } from 'vue';

    import Cropper from 'cropperjs';
    import VueCropper from 'vue-cropperjs';
    import 'cropperjs/dist/cropper.css';

    export default {
        components: {
            BizButton,
            BizButtonIcon,
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
            cropper: { type:[Object, null], default: null },
            fileName: { type: [String, null], default: "" },
            isDebugMode: { type: Boolean, default: false },
            isProcessing: { type: Boolean, default: false },
            modelValue: { type: [String, null], default: "" },
            dimension: { type: Object, default: () => {} },
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
                icon: {
                    checkCircle,
                    crop,
                    flipHorizontal,
                    flipVertical,
                    rotateLeft,
                    rotateRight,
                },
                aspectRatioOptions: [
                    {id: "Free", aspectRatio: null},
                    {id: "16:9", aspectRatio: 16/9},
                    {id: "4:3", aspectRatio: 4/3},
                    {id: "1:1", aspectRatio: 1},
                ],
                isOnReadyCachingIsNeeded: ref(true),
                onReadySkipApplyCache: ref(false),
            };
        },

        data() {
            return {
                aspectRatio: null,
                state: null,
                stateOptions: {
                    crop: "crop",
                    none: null,
                },

                cacheData: null,

                meta: null,
                afterReady: () => {},
            }
        },

        computed: {
            isCropState() {
                return this.state === this.stateOptions.crop;
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
                const defaultOptions = {
                    checkCrossOrigin: true,
                    dragMode: "move",
                    minContainerHeight: 400,
                    toggleDragModeOnDblclick: false,
                    viewMode: 1,
                };

                if (this.hasDimension) {
                    return {
                        ...defaultOptions,
                        ...{
                            autoCrop: true,
                            autoCropArea: this.ratio > 0.95 ? 0.95 : this.ratio,
                            cropBoxMovable: false,
                            cropBoxResizable: false,
                            initialAspectRatio: this.ratio,
                            zoomable: true,
                        },
                    };
                }

                return {
                    ...defaultOptions,
                    ...{
                        autoCrop: false,
                        autoCropArea: 0.9,
                        checkCrossOrigin: true,
                        zoomable: false,
                    }
                };
            },
        },
        async mounted() {
            this.imageCropper = this.$refs.cropper;

            const img = await this.getMeta(
                this.$refs.cropper.$refs.img.src
            );

            this.meta = {
                width: img.naturalWidth,
                height: img.naturalHeight,
            };
        },
        methods: {
            async getMeta(url) {
                const img = new Image();
                img.src = url;
                await img.decode();
                return img
            },

            cacheCropperData() {
                this.cacheData = this.imageCropper.getData();
            },

            applyCropperData() {
                this.imageCropper.setData(this.cacheData);
            },

            enableCropState() {
                this.state = this.stateOptions.crop;

                this.setAspectRatio(null);

                this.imageCropper.initCrop()
            },

            quitCropState() {
                this.disabledState();
            },

            disableState(options) {
                const { recreateOptions, disableRecreate } = options;

                if (! disableRecreate) {
                    this.recreate(recreateOptions);
                }

                this.afterReady = () => {
                    if (this.hasDimension) {
                        this.imageCropper
                            .reset()
                            .setDragMode('crop');
                    }

                    this.imageCropper.clear();
                    this.aspectRatio = null;
                    this.state = null;
                };
            },

            recreate(options) {
                this.imageCropper.destroy();

                this.imageCropper.cropper = new Cropper(
                    this.imageCropper.$refs.img,
                    options ?? this.cropperOptions
                );
            },

            async cropAndRecreate() {
                this.$refs.cropper.$refs.img.src = this.imageCropper.getCroppedCanvas().toDataURL();

                this.onReadySkipApplyCache = true;
                this.isOnReadyCachingIsNeeded = true;

                this.disableState({
                    recreateOptions: {
                        ...this.cropperOptions,
                        ...{
                            zoomable: false,
                        },
                    },
                });
            },

            rotateRight() {
                this.imageCropper.rotate(90);

                this.cacheCropperData();
            },

            rotateLeft() {
                this.imageCropper.rotate(-90);

                this.cacheCropperData();
            },

            flipY(event) {
                const dom = event.currentTarget;
                let scale = dom.getAttribute('data-scale');
                scale = scale ? -scale : -1;
                this.imageCropper.scaleY(scale);
                dom.setAttribute('data-scale', scale);

                this.cacheCropperData();
            },

            flipX(event) {
                const dom = event.currentTarget;
                let scale = dom.getAttribute('data-scale');
                scale = scale ? -scale : -1;
                this.imageCropper.scaleX(scale);
                dom.setAttribute('data-scale', scale);

                this.cacheCropperData();
            },

            cropperReady() {
                if (this.isCropState) {
                    this.imageCropper.initCrop();
                }

                if (this.isOnReadyCachingIsNeeded) {

                    this.cacheCropperData();
                }

                if (! this.onReadySkipApplyCache) {
                    this.applyCropperData();
                }

                this.afterReady();

                this.isOnReadyCachingIsNeeded = false;
                this.onReadySkipApplyCache = false;
                this.afterReady = () => {};
            },

            setAspectRatio(ratio) {
                let minCropBoxWidth = 500;

                if (this.meta.width < minCropBoxWidth) {
                    minCropBoxWidth = this.meta.width;
                }

                const defaultOptions = {
                    ...this.cropperOptions,
                    ...{
                        dragMode: "move",
                        zoomable: true,
                    },
                };

                if (ratio != null && this.aspectRatio == null) {

                    this.afterReady = () => {
                        this.applyCropperData();
                    };

                    this.recreate({
                        ...defaultOptions,
                        ...{
                            autoCropArea: 0.9,
                            cropBoxMovable: false,
                            cropBoxResizable: false,
                            minCropBoxWidth,
                        },
                    });
                }

                if (ratio === null) {
                    this.afterReady = () => {
                        this.applyCropperData();

                        const canvasData = this.imageCropper.getCanvasData();
                        const width = canvasData.width * 7 / 10;
                        const height = canvasData.height * 6 / 10;

                        this.imageCropper.setCropBoxData({
                            width,
                            height,
                            left: (canvasData.width - width) / 2,
                            top: (canvasData.height - height) / 2,
                        })
                    };

                    this.recreate({
                        ...defaultOptions,
                        ...{
                            autoCropArea: 0.9,
                            cropBoxResizable: true,
                        },
                    });
                }

                this.imageCropper.setAspectRatio(ratio ?? null);
                this.aspectRatio = ratio;
            },
        },
    };
</script>
