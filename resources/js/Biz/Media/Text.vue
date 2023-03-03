<template>
    <div
        :style="isScrolled ? scrollClass : {}"
    >
        <div
            v-for="medium in media"
            :key="medium.id"
            class="mb-2"
        >
            <biz-media-text-item
                :medium="medium"
                :is-delete-enabled="isDeleteEnabled"
                :is-download-enabled="isDownloadEnabled"
                :is-preview-enabled="isPreviewEnabled"
                @on-delete-clicked="$emit('on-delete-clicked', $event)"
                @on-preview-clicked="onPreviewClicked"
            />
        </div>

        <biz-modal
            v-show="isModalOpen"
            @close="closeModal()"
        >
            <p class="image">
                <img
                    :src="previewImageSrc"
                    alt="preview-image"
                >
            </p>
        </biz-modal>
    </div>
</template>

<script>
    import HasModalMixin from '@/Mixins/HasModal';
    import BizMediaTextItem from '@/Biz/Media/TextItem.vue';
    import BizModal from '@/Biz/Modal.vue';

    export default {
        name: 'MediaText',

        components: {
            BizMediaTextItem,
            BizModal,
        },

        mixins: [
            HasModalMixin,
        ],

        props: {
            isDeleteEnabled: { type: Boolean, default: true },
            isDownloadEnabled: { type: Boolean, default: true },
            isEditEnabled: { type: Boolean, default: true },
            isPreviewEnabled: { type: Boolean, default: true },
            isScrolled: { type: Boolean, default: false },
            maxHeight: { type: Number, default: 300 },
            media: { type: Array, default: () => [] },
        },

        emits: [
            'on-delete-clicked',
            'on-preview-clicked',
        ],

        data() {
            return {
                previewImageSrc: null,
            };
        },

        computed: {
            scrollClass() {
                return {
                    'max-height': this.maxHeight.toString() + 'px' ,
                    'overflow-y': 'scroll',
                    'overflow-x': 'hidden',
                };
            },
        },

        methods: {
            onPreviewClicked(media) {
                this.previewImageSrc = media.file_url;
                this.openModal();
            },
        },
    };
</script>
