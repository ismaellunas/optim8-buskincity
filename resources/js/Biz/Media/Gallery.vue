<template>
    <div>
        <div
            class="columns is-multiline"
            :style="isScrolled ? scrollClass : {}"
        >
            <div
                v-for="medium in media"
                :key="medium.id"
                :class="columnClass"
            >
                <biz-media-gallery-item
                    :medium="medium"
                    :is-delete-enabled="isDeleteEnabled"
                    :is-download-enabled="isDownloadEnabled"
                    :is-edit-enabled="isEditEnabled"
                    :is-preview-enabled="isPreviewEnabled"
                    :is-select-enabled="isSelectEnabled"
                    @on-delete-clicked="$emit('on-delete-clicked', $event)"
                    @on-edit-clicked="$emit('on-edit-clicked', $event)"
                    @on-select-clicked="$emit('on-select-clicked', $event)"
                    @on-preview-clicked="onPreviewClicked"
                >
                    <template
                        #itemActions="{ mediumItem }"
                    >
                        <slot
                            name="itemActions"
                            :medium-item="mediumItem"
                        />
                    </template>
                </biz-media-gallery-item>
            </div>
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
    import BizMediaGalleryItem from '@/Biz/Media/GalleryItem.vue';
    import BizModal from '@/Biz/Modal.vue';

    export default {
        name: 'MediaGallery',

        components: {
            BizMediaGalleryItem,
            BizModal,
        },

        mixins: [
            HasModalMixin,
        ],

        props: {
            columnClass: { type: Array, default: () => ['column','is-3'] },
            isDeleteEnabled: { type: Boolean, default: true },
            isDownloadEnabled: { type: Boolean, default: true },
            isEditEnabled: { type: Boolean, default: true },
            isPreviewEnabled: { type: Boolean, default: true },
            isSelectEnabled: { type: Boolean, default: false },
            isScrolled: { type: Boolean, default: false },
            maxHeight: { type: Number, default: 700 },
            media: { type: Array, default: () => [] },
        },

        emits: [
            'on-delete-clicked',
            'on-edit-clicked',
            'on-select-clicked',
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
