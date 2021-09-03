<template>
    <tr>
        <td>
            <figure
                v-if="medium.is_image"
                class="image is-48x48"
            >
                <img :src="medium.thumbnail_url">
            </figure>
            <span 
                v-else
                class="icon is-large has-text-centered"
            >
                <span class="fa-stack fa-lg">
                    <i :class="['fas', thumbnailIcon, 'fa-2x']"></i>
                </span>
            </span>
        </td>
        <td><b>{{ medium.file_name }}</b></td>
        <td>{{ medium.date_modified }}</td>
        <td>{{ type }}</td>
        <td>{{ medium.readable_size }}</td>
        <td>
            <div class="level-right">
                <sdb-button-icon
                    v-if="isImage"
                    :class="[actionClass, 'is-info']"
                    icon="fas fa-expand"
                    title="Preview"
                    type="button"
                    @click="$emit('on-preview-clicked', medium)"
                />
                <sdb-button-icon
                    v-if="isEditEnabled"
                    :class="[actionClass, 'is-primary']"
                    icon="fas fa-pen"
                    type="button"
                    @click="$emit('on-edit-clicked', medium)"
                />
                <sdb-button-icon
                    v-if="isDeleteEnabled"
                    :class="[actionClass, 'is-danger']"
                    icon="far fa-trash-alt"
                    title="Delete"
                    type="button"
                    @click="$emit('on-delete-clicked', medium)"
                />
                <sdb-button-download
                    v-if="isDownloadEnabled"
                    :class="[actionClass, 'is-danger']"
                    title="Download"
                    type="button"
                    :url="medium.file_url"
                />

                <slot name="actions" :medium="medium"></slot>
            </div>
        </td>
    </tr>
</template>

<script>
    import SdbButtonDownload from '@/Sdb/ButtonDownload';
    import SdbButtonIcon from '@/Sdb/ButtonIcon';

    export default {
        name: 'MediaListItem',
        components: {
            SdbButtonDownload,
            SdbButtonIcon,
        },
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
                actionClass: "is-borderless is-shadowless is-inverted",
            };
        },
        computed: {
            isImage() {
                return (
                    (this.medium?.is_image)
                    || (this.medium?.file && this.medium.file.type.startsWith("image"))
                );
            },
            type() {
                if (this.medium.is_image) {
                    return 'Image';
                }
                return 'File';
            },
            thumbnailIcon() {
                if (this.medium.file_type === "video") {
                    return "far fa-file-video";
                } else if (this.medium.extension) {
                    if (this.medium.extension === "pdf") {
                        return "far fa-file-pdf";
                    } else if (this.medium.extension.startsWith('doc')) {
                        return "far fa-file-word";
                    } else if (this.medium.extension.startsWith('ppt')) {
                        return "far fa-file-powerpoint";
                    } else if (this.medium.extension.startsWith('xls')) {
                        return "far fa-file-excel";
                    }
                }
                return "far fa-file-alt";
            },
        },
    }
</script>
