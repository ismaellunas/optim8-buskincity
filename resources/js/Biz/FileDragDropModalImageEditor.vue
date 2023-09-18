<template>
    <biz-modal-image-editor
        v-model="computedMediumUrl"
        v-model:cropper="cropper"
        :cropped-image-type="croppedImageType"
        :file-name="computedMedium.name"
        :dimension="dimension"
        @close="closeModal()"
    >
        <template #leftActions>
            <biz-button
                type="button"
                @click="closeModal()"
            >
                Cancel
            </biz-button>
        </template>

        <template #actions>
            <biz-button
                type="button"
                class="is-primary"
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
    import { computed, ref } from 'vue';
    import { getBlob, getCanvas } from '@/Libs/crop-helper';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: "BizFileDragDropImageEditor",

        components: {
            BizButton,
            BizModalImageEditor,
        },

        props: {
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
            const computedMedium = useModelWrapper(props, emit, 'medium');

            return {
                computedMedium,
                computedMediumUrl: props.mediumUrl
                    ? useModelWrapper(props, emit, 'mediumUrl')
                    : URL.createObjectURL(computedMedium.value),
            };
        },

        data() {
            return {
                cropper: null,
            };
        },

        computed: {
            croppedImageType() {
                let imageType = null;

                if (this.medium?.type) {
                    imageType = this.medium.type;
                } else if (this.medium.extension) {
                    imageType = 'image/' + this.medium.extension;
                }

                return imageType == 'image/png' ? imageType : 'image/jpeg';
            },
        },

        methods: {
            async updateFile() {
                this.computedMediumUrl = getCanvas(this.cropper, 600).toDataURL('image/jpeg', 0.8);
                this.computedMedium = getBlob(this.cropper, this.croppedImageType);

                this.closeModal();
            },

            closeModal() {
                this.$emit('on-close');
            },
        },
    }
</script>
