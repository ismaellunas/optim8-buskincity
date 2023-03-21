<template>
    <div class="table-container">
        <biz-table class="is-striped is-hoverable is-fullwidth">
            <thead>
                <tr>
                    <th />
                    <th>File Name</th>
                    <th>Date Modified</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>
                        <div class="level-right">
                            Actions
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <template
                    v-for="medium in media"
                    :key="medium.id"
                >
                    <biz-media-list-item
                        :medium="medium"
                        :is-delete-enabled="isDeleteEnabled"
                        :is-download-enabled="isDownloadEnabled"
                        :is-edit-enabled="isEditEnabled"
                        :is-preview-enabled="isPreviewEnabled"
                        @on-delete-clicked="$emit('on-delete-clicked', $event)"
                        @on-edit-clicked="$emit('on-edit-clicked', $event)"
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
                    </biz-media-list-item>
                </template>
            </tbody>
        </biz-table>

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
    import BizTable from '@/Biz/Table.vue';
    import BizMediaListItem from '@/Biz/Media/ListItem.vue';
    import BizModal from '@/Biz/Modal.vue';

    export default {
        name: 'MediaList',

        components: {
            BizTable,
            BizMediaListItem,
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
            media: { type: Array, default: () => [] },
        },

        emits: [
            'on-delete-clicked',
            'on-edit-clicked',
        ],

        data() {
            return {
                previewImageSrc: null,
            };
        },

        methods: {
            onPreviewClicked(media) {
                this.previewImageSrc = media.file_url;
                this.openModal();
            },
        },
    };
</script>
