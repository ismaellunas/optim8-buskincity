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
                icon="far fa-trash-alt"
                title="Delete"
                type="button"
                :class="['is-danger']"
                @click="$emit('on-delete-clicked', medium)"
            />
        </div>
    </div>
</template>

<script>
    import BizButtonDownload from '@/Biz/ButtonDownload';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import MixinMediaItem from '@/Mixins/MediaItem';

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
            medium: { type: Object, required: true },
            isDeleteEnabled: {type: Boolean, default: true},
            isDownloadEnabled: {type: Boolean, default: true},
        },

        emits: [
            'on-delete-clicked',
        ],
    };
</script>
