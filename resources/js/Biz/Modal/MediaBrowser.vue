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
            <biz-pagination
                :is-ajax="true"
                :links="data?.links ?? []"
                @on-clicked-pagination="$emit('on-clicked-pagination', $event)"
            />
        </template>

        <biz-media-library
            :accepted-types="acceptedTypes"
            :is-ajax="true"
            :is-delete-enabled="false"
            :is-download-enabled="isDownloadEnabled"
            :is-edit-enabled="false"
            :is-pagination-displayed="false"
            :is-upload-enabled="isUploadEnabled"
            :query-params="queryParams"
            :records="data"
            :search="search"
            @on-media-submitted="$emit('on-media-submitted', $event)"
            @on-view-changed="$emit('on-view-changed', $event)"
        >
            <template
                #itemActions="{ mediumItem }"
            >
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
        </biz-media-library>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizMediaLibrary from '@/Biz/MediaLibrary.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import BizPagination from '@/Biz/Pagination.vue';
    import { acceptedImageTypes } from '@/Libs/defaults';

    export default {
        name: 'BizMediaBrowser',

        components: {
            BizButton,
            BizButtonIcon,
            BizMediaLibrary,
            BizModalCard,
            BizPagination,
        },

        props: {
            acceptedFileType: { type: Array, default: null },
            data: { type: Object, required: true },
            isDownloadEnabled: { type: Boolean, default: true },
            isUploadEnabled: { type: Boolean, default: true },
            queryParams: { type: Object, default: () => {} },
            search: { type: Function, required: true },
            title: { type: String, default: 'Images' },
        },

        emits: [
            'close',
            'on-clicked-pagination',
            'on-media-selected',
            'on-media-submitted',
            'on-view-changed',
        ],

        setup(props) {
            return {
                acceptedTypes: props.acceptedFileType ?? acceptedImageTypes,
            };
        },
    }
</script>
