<template>
    <div
        class="card card-equal-height"
        :class="cardClasses"
    >
        <div class="card-image px-2 pt-2 has-text-centered">
            <biz-image
                v-if="isImage"
                :alt="medium.display_file_name"
                :src="imgSrc"
                :ratio="ratio"
                :style="imageStyles"
                :figure-styles="figureStyles"
            />
            <span
                v-else
                class="icon is-large"
            >
                <span class="fa-stack fa-lg">
                    <i :class="[thumbnailIcon, 'fa-5x']" />
                </span>
            </span>
        </div>

        <div
            v-if="isFilenameShown"
            class="card-content p-2"
        >
            <div
                class="content"
                style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis;"
            >
                <p>{{ medium.display_file_name }}</p>
            </div>
        </div>

        <footer class="card-footer">
            <biz-button-icon
                v-if="isImage"
                title="Preview"
                type="button"
                :class="[actionClass, 'is-info']"
                :icon="icon.expand"
                @click="$emit('on-preview-clicked', medium)"
            />
            <biz-button-icon
                v-if="isEditButtonEnabled"
                title="Edit"
                type="button"
                :class="[actionClass, 'is-primary']"
                :icon="icon.edit"
                @click="$emit('on-edit-clicked', medium)"
            />
            <biz-button-icon
                v-if="isDeleteEnabled"
                title="Delete"
                type="button"
                :class="[actionClass, 'is-danger']"
                :icon="icon.remove"
                @click="$emit('on-delete-clicked', medium)"
            />
            <biz-button-download
                v-if="isDownloadEnabled"
                title="Download"
                type="button"
                :class="[actionClass, 'is-link']"
                :url="medium.file_url"
            />

            <slot
                name="itemActions"
                :medium-item="medium"
            />
        </footer>
    </div>
</template>

<script>
    import MixinMediaItem from '@/Mixins/MediaItem';
    import BizButtonDownload from '@/Biz/ButtonDownload.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizImage from '@/Biz/Image.vue';
    import { isEmpty } from 'lodash';
    import { expand, edit, remove } from '@/Libs/icon-class';

    export default {
        name: 'MediaGalleryItem',

        components: {
            BizButtonDownload,
            BizButtonIcon,
            BizImage,
        },

        mixins: [
            MixinMediaItem,
        ],

        inject: {
            selectedMedia: { default: () => {} }
        },

        props: {
            isDeleteEnabled: { type: Boolean, default: true },
            isDownloadEnabled: { type: Boolean, default: true },
            isEditButtonForImage: { type: Boolean, default: false },
            isEditEnabled: { type: Boolean, default: true },
            isPreviewEnabled: { type: Boolean, default: true },
            isSelectEnabled: { type: Boolean, default: true },
            medium: { type: Object, default: () => {}},
            isImagePreviewThumbnail: { type:Boolean, default: true },
            ratio: { type: String, default: null },
            imageStyles: { type: [String, Object, Array], default: null },
            figureStyles: { type: [String, Object, Array], default: null },
            isFilenameShown: { type: Boolean, default: true },
        },

        emits: [
            'on-delete-clicked',
            'on-edit-clicked',
            'on-preview-clicked',
        ],

        setup() {
            return {
                icon: { expand, edit, remove },
                actionClass: "card-footer-item p-2 is-borderless is-shadowless is-inverted",
            };
        },

        computed: {
            cardClasses() {
                if (
                    !isEmpty(this.selectedMedia)
                    && this.isSelectEnabled
                ) {
                    return {
                        'selected': this.selectedMedia.mediaIds.includes(this.medium.id),
                    };
                }

                return {};
            },

            imgSrc() {
                if (this.isImagePreviewThumbnail) {
                    return this.medium.thumbnail_url ?? this.medium.file_url;
                }

                return this.medium.optimized_image_url ?? this.medium.file_url;
            },

            isEditButtonEnabled() {
                if (! this.isEditButtonForImage) {
                    return this.isEditEnabled;
                } else {
                    return (
                        this.isEditEnabled
                        && this.isImage
                    );
                }
            },
        },
    }
</script>
