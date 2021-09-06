<template>
    <div class="card card-equal-height">
        <div class="card-image px-2 pt-2 has-text-centered">
            <sdb-image
                v-if="isImage"
                :alt="medium.file_name_without_extension"
                :src="medium.thumbnail_url"
            />
            <span
                v-else
                class="icon is-large"
            >
                <span class="fa-stack fa-lg">
                    <i :class="[thumbnailIcon, 'fa-5x']"></i>
                </span>
            </span>
        </div>

        <div class="card-content p-2">
            <div
                class="content"
                style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis;"
            >
                <p>{{ medium.file_name_without_extension }}</p>
            </div>
        </div>

        <footer class="card-footer">

            <sdb-button-icon
                v-if="isImage"
                icon="fas fa-expand"
                title="Preview"
                type="button"
                :class="[actionClass, 'is-info']"
                @click="$emit('on-preview-clicked', medium)"
            />
            <sdb-button-icon
                v-if="isEditEnabled"
                icon="fas fa-pen"
                type="button"
                :class="[actionClass, 'is-primary']"
                @click="$emit('on-edit-clicked', medium)"
            />
            <sdb-button-icon
                v-if="isDeleteEnabled"
                icon="far fa-trash-alt"
                title="Delete"
                type="button"
                :class="[actionClass, 'is-danger']"
                @click="$emit('on-delete-clicked', medium)"
            />
            <sdb-button-download
                v-if="isDownloadEnabled"
                title="Download"
                type="button"
                :class="[actionClass, 'is-link']"
                :url="medium.file_url"
            />

            <slot name="actions" :medium="medium"></slot>
        </footer>
    </div>
</template>

<script>
    import MixinMediaItem from '@/Mixins/MediaItem';
    import SdbButtonDownload from '@/Sdb/ButtonDownload';
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbImage from '@/Sdb/Image';

    export default {
        name: 'MediaGalleryItem',
        components: {
            SdbButtonDownload,
            SdbButtonIcon,
            SdbImage,
        },
        mixins: [
            MixinMediaItem,
        ],
        props: {
            isDeleteEnabled: {type: Boolean, default: true},
            isDownloadEnabled: {type: Boolean, default: true},
            isEditEnabled: {type: Boolean, default: true},
            isPreviewEnabled: {type: Boolean, default: true},
            medium: Object,
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
    }
</script>

<style scoped>
</style>
