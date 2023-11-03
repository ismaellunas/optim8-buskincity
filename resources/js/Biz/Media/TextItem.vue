<template>
    <div class="field has-addons">
        <div class="control is-expanded">
            <input
                class="input"
                disabled
                readonly
                type="text"
                :value="medium.display_file_name"
            >
        </div>

        <div class="control">
            <biz-button-icon
                v-if="isImage"
                title="Preview"
                type="button"
                :class="['is-info']"
                :icon="icon.expand"
                @click="$emit('on-preview-clicked', medium)"
            />
        </div>

        <div class="control">
            <biz-button-download
                v-if="isDownloadEnabled"
                title="Download"
                type="button"
                :class="['is-link']"
                :url="medium.file_url"
            />
        </div>

        <div class="control">
            <biz-button-icon
                v-if="isDeleteEnabled"
                title="Delete"
                type="button"
                :class="['is-danger']"
                :icon="icon.remove"
                @click="$emit('on-delete-clicked', medium)"
            />
        </div>
    </div>
</template>

<script>
    import MixinMediaItem from '@/Mixins/MediaItem';
    import BizButtonDownload from '@/Biz/ButtonDownload.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import { expand, remove } from '@/Libs/icon-class';

    export default {
        name: 'BizMediaTextItem',

        components: {
            BizButtonDownload,
            BizButtonIcon,
        },

        mixins: [
            MixinMediaItem,
        ],

        props: {
            isDeleteEnabled: {type: Boolean, default: true},
            isDownloadEnabled: {type: Boolean, default: true},
            isPreviewEnabled: { type: Boolean, default: true },
            medium: { type: Object, required: true },
        },

        emits: [
            'on-delete-clicked',
            'on-preview-clicked',
        ],

        setup() {
            return {
                icon: { expand, remove },
            };
        },
    };
</script>
