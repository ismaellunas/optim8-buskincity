<template>
    <biz-modal-card
        :content-class="['is-huge']"
        :is-close-hidden="true"
        @close="$emit('close')"
    >
        <template #header>
            <p class="modal-card-title">
                {{ title }}
            </p>
            <biz-button
                aria-label="close"
                class="delete is-primary"
                type="button"
                @click="$emit('close')"
            />
        </template>

        <template #footer>
            <div
                class="columns"
                style="width: 100%"
            >
                <div class="column">
                    <biz-pagination
                        :is-ajax="true"
                        :links="data?.links ?? []"
                        :current-page="data.current_page"
                        :last-page="data.last_page"
                        @on-clicked-pagination="$emit('on-clicked-pagination', $event)"
                    />
                </div>

                <div
                    v-if="allowMultiple"
                    class="column has-text-right"
                >
                    <biz-button
                        type="button"
                        class="is-primary"
                        @click="$emit('on-multiple-media-selected')"
                    >
                        Done
                    </biz-button>
                </div>
            </div>
        </template>

        <biz-media-library
            :accepted-types="acceptedTypes"
            :allow-multiple="allowMultiple"
            :is-ajax="true"
            :is-delete-enabled="false"
            :is-download-enabled="isDownloadEnabled"
            :is-edit-enabled="false"
            :is-pagination-displayed="false"
            :is-upload-enabled="isUploadEnabled"
            :query-params="queryParams"
            :records="data"
            :search="search"
            :max-files="maxFiles"
            :max-file-size="maxFileSize"
            :instructions="instructions"
            @on-media-submitted="$emit('on-media-submitted', $event)"
            @on-view-changed="$emit('on-view-changed', $event)"
        >
            <template
                #itemActions="{ mediumItem }"
            >
                <template v-if="!allowMultiple">
                    <biz-button-icon
                        icon="fas fa-check"
                        title="Select"
                        type="button"
                        :class="{'is-borderless is-shadowless is-inverted is-primary': true, 'card-footer-item  p-2': queryParams.view !== 'list'}"
                        @click="$emit('on-media-selected', mediumItem, $event)"
                    >
                        <span>Select</span>
                    </biz-button-icon>
                </template>

                <template v-else>
                    <biz-checkbox
                        v-model:checked="computedSelectedMedia.mediaIds"
                        :class="{'card-footer-item  p-2': queryParams.view !== 'list', 'mt-1 p-3': queryParams.view === 'list'}"
                        :value="mediumItem.id"
                        :disabled="(isMaximumSelected && !computedSelectedMedia.mediaIds.includes(mediumItem.id))"
                        @change="updateMediaSelected(mediumItem)"
                    />
                </template>
            </template>
        </biz-media-library>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizMediaLibrary from '@/Biz/MediaLibrary.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import BizPagination from '@/Biz/Pagination.vue';
    import { acceptedImageTypes } from '@/Libs/defaults';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizMediaBrowser',

        components: {
            BizButton,
            BizButtonIcon,
            BizCheckbox,
            BizMediaLibrary,
            BizModalCard,
            BizPagination,
        },

        props: {
            acceptedFileType: { type: Array, default: null },
            allowMultiple: { type: Boolean, default: false, },
            data: { type: Object, required: true },
            instructions: { type: Array, default: () => [] },
            isDownloadEnabled: { type: Boolean, default: true },
            isUploadEnabled: { type: Boolean, default: true },
            maxFiles: { type: Number, default: 1, },
            maxFileSize: { type: [String, Number], default: null, },
            queryParams: { type: Object, default: () => {} },
            search: { type: Function, required: true },
            selectedMedia: { type: Object, default: () => {} },
            title: { type: String, default: 'Images' },
        },

        emits: [
            'close',
            'on-clicked-pagination',
            'on-media-selected',
            'on-media-submitted',
            'on-multiple-media-selected',
            'on-view-changed',
            'update:selectedMedia',
        ],

        setup(props, {emit}) {
            return {
                acceptedTypes: props.acceptedFileType ?? acceptedImageTypes,
                computedSelectedMedia: useModelWrapper(props, emit, 'selectedMedia'),
            };
        },

        computed: {
            isMaximumSelected() {
                return this.maxFiles == 0;
            },
        },

        methods: {
            updateMediaSelected(mediaItem) {
                let findIndex = this.computedSelectedMedia.media.findIndex(media => media.id === mediaItem.id);

                if (findIndex === -1) {
                    this.computedSelectedMedia.media.push(mediaItem);
                } else {
                    this.computedSelectedMedia.media.splice(findIndex, 1);
                }
            },
        },
    }
</script>
