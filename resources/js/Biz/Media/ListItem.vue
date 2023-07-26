<template>
    <tr :class="trClasses">
        <td>
            <biz-image
                v-if="medium.is_image"
                square="is-48x48"
                :src="medium.thumbnail_url"
            />
            <span
                v-else
                class="icon is-large has-text-centered"
            >
                <span class="fa-stack fa-lg">
                    <i :class="['fas', thumbnailIcon, 'fa-2x']" />
                </span>
            </span>
        </td>
        <td class="has-text-weight-bold">
            {{ medium.file_name }}
        </td>
        <td>{{ medium.date_modified }}</td>
        <td>{{ type }}</td>
        <td>{{ medium.readable_size }}</td>
        <td>
            <div class="level-right">
                <biz-button-icon
                    v-if="isImage"
                    :class="[actionClass, 'is-info']"
                    icon="fas fa-expand"
                    title="Preview"
                    type="button"
                    @click="$emit('on-preview-clicked', medium)"
                />
                <biz-button-icon
                    v-if="isEditEnabled"
                    :class="[actionClass, 'is-primary']"
                    icon="fas fa-pen"
                    title="Edit"
                    type="button"
                    @click="$emit('on-edit-clicked', medium)"
                />
                <biz-button-icon
                    v-if="isDeleteEnabled"
                    :class="[actionClass, 'is-danger']"
                    icon="far fa-trash-alt"
                    title="Delete"
                    type="button"
                    @click="$emit('on-delete-clicked', medium)"
                />
                <biz-button-download
                    v-if="isDownloadEnabled"
                    :class="[actionClass, 'is-link']"
                    title="Download"
                    type="button"
                    :url="medium.file_url"
                />

                <slot
                    name="itemActions"
                    :medium-item="medium"
                />
            </div>
        </td>
    </tr>
</template>

<script>
    import MixinMediaItem from '@/Mixins/MediaItem';
    import BizButtonDownload from '@/Biz/ButtonDownload.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizImage from '@/Biz/Image.vue';
    import { isEmpty } from 'lodash';

    export default {
        name: 'MediaListItem',

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
            isDeleteEnabled: {type: Boolean, default: true},
            isDownloadEnabled: {type: Boolean, default: true},
            isEditEnabled: {type: Boolean, default: true},
            isPreviewEnabled: {type: Boolean, default: true},
            isSelectEnabled: { type: Boolean, default: true },
            medium: { type: Object, default: () => {}},
        },

        emits: [
            'on-delete-clicked',
            'on-edit-clicked',
            'on-preview-clicked',
        ],

        data() {
            return {
                actionClass: "is-borderless is-shadowless is-inverted",
            };
        },

        computed: {
            type() {
                if (this.isImage) {
                    return 'Image';
                }
                return 'File';
            },

            trClasses() {
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
        },
    }
</script>
