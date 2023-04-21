<template>
    <div class="table-container">
        <biz-table class="is-striped is-hoverable is-fullwidth">
            <thead>
                <tr>
                    <th />
                    <th>{{ capitalCase(i18n.file_name )}}</th>
                    <th>{{ capitalCase(i18n.date_modified) }}</th>
                    <th>{{ i18n.type }}</th>
                    <th>{{ i18n.size }}</th>
                    <th>
                        <div class="level-right">
                            {{ i18n.actions }}
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
    import { capitalCase } from 'change-case';

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

        inject: {
            i18n: {
                default: () => ({
                    file_name : 'File name',
                    date_modified : 'Date modified',
                    type : 'Type',
                    size : 'Size',
                    actions : 'Actions',
                })
            },
        },

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

            capitalCase,
        },
    };
</script>
