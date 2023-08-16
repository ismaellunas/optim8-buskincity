<template>
    <biz-modal-image-editor
        v-model="computedMediumUrl"
        v-model:cropper="cropper"
        :cropped-image-type="croppedImageType"
        :file-name="computedMedium.name"
        :dimension="dimension"
        :is-resize-enabled="false"
        @close="closeModal()"
    >
        <template #actions>
            <biz-button
                type="button"
                class="is-link"
                @click="updateFile()"
            >
                Done
            </biz-button>
        </template>
    </biz-modal-image-editor>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizModalImageEditor from '@/Biz/Modal/ImageEditor.vue';
    import { getCanvasBlob, useModelWrapper } from '@/Libs/utils';

    export default {
        name: "BizFileDragDropImageEditor",

        components: {
            BizButton,
            BizModalImageEditor,
        },

        props: {
            croppedImageType: { type: String, default: "image/png" },
            dimension: { type: Object, default: () => {} },
            medium: { type: Object, required: true },
            mediumUrl: { type: String, default: null },
        },

        emits: [
            'on-close',
            'update:medium',
            'update:mediumUrl',
        ],

        setup(props, {emit}) {
            return {
                computedMedium: useModelWrapper(props, emit, 'medium'),
                computedMediumUrl: props.mediumUrl
                    ? useModelWrapper(props, emit, 'mediumUrl')
                    : URL.createObjectURL(props.medium),
            };
        },

        data() {
            return {
                cropper: null,
            };
        },

        methods: {
            updateFile() {
                const self = this;

                self.getCropperBlob()
                    .then((blob) => {
                        self.computedMediumUrl = URL.createObjectURL(blob);
                        self.computedMedium = blob;

                        self.closeModal();
                    });
            },

            getCropperBlob() {
                return getCanvasBlob(
                    this.cropper.getCroppedCanvas(),
                    this.croppedImageType
                );
            },

            closeModal() {
                this.$emit('on-close');
            },
        },
    }
</script>