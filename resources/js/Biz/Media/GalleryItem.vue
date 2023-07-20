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

        <div class="card-content p-2">
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
                icon="fas fa-expand"
                title="Preview"
                type="button"
                :class="[actionClass, 'is-info']"
                @click="$emit('on-preview-clicked', medium)"
            />
            <biz-button-icon
                v-if="isEditEnabled"
                icon="fas fa-pen"
                title="Edit"
                type="button"
                :class="[actionClass, 'is-primary']"
                @click="$emit('on-edit-clicked', medium)"
            />
            <biz-button-icon
                v-if="isDeleteEnabled"
                icon="far fa-trash-alt"
                title="Delete"
                type="button"
                :class="[actionClass, 'is-danger']"
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
            isEditEnabled: { type: Boolean, default: true },
            isPreviewEnabled: { type: Boolean, default: true },
            isSelectEnabled: { type: Boolean, default: true },
            medium: { type: Object, default: () => {}},
            isImagePreviewThumbnail: { type:Boolean, default: true },
        },

        emits: [
            'on-delete-clicked',
            'on-edit-clicked',
            'on-preview-clicked',
        ],

        data() {
            return {
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
        },
    }
</script>
